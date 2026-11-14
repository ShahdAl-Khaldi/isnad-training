<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Application extends Model
{
    public const SUBMITTED = 'submitted';           // بانتظار المقابلة
    public const INTERVIEW = 'interview_scheduled'; // تحدد موعد مقابلة
    public const ACCEPTED = 'accepted';             // مقبول، بمهلة الدفع
    public const PAYMENT_SUBMITTED = 'payment_submitted'; // دفع بانتظار التأكيد
    public const CONFIRMED = 'confirmed';           // مقعد مثبّت
    public const EXPIRED = 'expired';               // انتهت المهلة
    public const WAITLISTED = 'waitlisted';         // قائمة الانتظار
    public const REJECTED = 'rejected';             // غير مقبول

    public const STATUS_LABELS = [
        self::PAYMENT_SUBMITTED => 'تحويل بحاجة تأكيد',
        self::CONFIRMED => 'مقعد مثبّت',
        self::SUBMITTED => 'بانتظار الدفع',
        self::ACCEPTED => 'بانتظار إعادة رفع التحويل',
        self::INTERVIEW => 'بانتظار الدفع (طلب قديم)',
        self::EXPIRED => 'انتهت المهلة',
        self::WAITLISTED => 'قائمة الانتظار',
        self::REJECTED => 'مرفوض',
    ];

    /** الحالات اللي الطالب فيها لازم يدفع / يرفع تحويل */
    public const AWAITING_PAYMENT = [self::SUBMITTED, self::INTERVIEW, self::ACCEPTED];

    public const STATUS_TONES = [
        self::SUBMITTED => 'orange',
        self::INTERVIEW => 'orange',
        self::ACCEPTED => 'orange',
        self::PAYMENT_SUBMITTED => 'yellow',
        self::CONFIRMED => 'green',
        self::EXPIRED => 'red',
        self::WAITLISTED => 'gray',
        self::REJECTED => 'red',
    ];

    /** الحالات اللي بتحجز مقعد بالمسار */
    public const HOLDS_SEAT = [self::ACCEPTED, self::PAYMENT_SUBMITTED, self::CONFIRMED];

    protected $fillable = [
        'name', 'phone', 'university', 'major', 'track', 'hours', 'portfolio_url', 'eligible', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'eligible' => 'boolean',
            'interview_at' => 'datetime',
            'accepted_at' => 'datetime',
            'payment_deadline' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'expired_at' => 'datetime',
            'waitlisted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Application $app) {
            $app->token ??= Str::random(40);
            if (! $app->code) {
                do {
                    $code = 'ISN-'.random_int(10000, 99999);
                } while (static::where('code', $code)->exists());
                $app->code = $code;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // ===== Helpers =====

    public function trackName(): string
    {
        return config("isnad.tracks.{$this->track}", $this->track);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusTone(): string
    {
        return self::STATUS_TONES[$this->status] ?? 'gray';
    }

    /** رقم الواتس بصيغة دولية بدون + */
    public function whatsappNumber(): string
    {
        return static::normalizePhone($this->phone);
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = '970'.substr($digits, 1);
        }

        return $digits;
    }

    /** توحيد كتابة العربي للمقارنة (أ/إ/آ، ة/ه، ى/ي، "جامعة"، "ال"، المسافات) */
    public static function normalizeArabic(string $text): string
    {
        $t = mb_strtolower(trim($text));
        $t = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $t);
        $t = strtr($t, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي']);
        $t = preg_replace('/(^|\s)(جامعه|الجامعه|university|of|the)(?=\s|$)/u', ' ', $t);
        $t = preg_replace('/(^|\s)ال/u', '$1', $t);

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $t);
    }

    public function whatsappLink(string $message): string
    {
        return 'https://wa.me/'.$this->whatsappNumber().'?text='.rawurlencode($message);
    }

    public function receiptIsImage(): bool
    {
        return (bool) ($this->receipt_path && preg_match('/\.(jpe?g|png|webp|gif)$/i', $this->receipt_path));
    }

    /** رابط موقّع ومؤقت لصورة التحويل (للإدارة بس) */
    public function receiptUrl(): ?string
    {
        return $this->receipt_path
            ? \Illuminate\Support\Facades\URL::temporarySignedRoute('admin.applications.receipt', now()->addMinutes(60), ['application' => $this->id, 'v' => substr(md5($this->receipt_path), 0, 8)])
            : null;
    }

    public function statusUrl(): string
    {
        return route('status.show', $this->token);
    }

    public function hoursLeft(): ?int
    {
        if (! $this->payment_deadline || $this->status !== self::ACCEPTED) {
            return null;
        }

        return max(0, (int) ceil(now()->diffInMinutes($this->payment_deadline, false) / 60));
    }

    /** رقم الخطوة الحالية بشريط التقدم (0: التسجيل والدفع، 1: مراجعة التحويل، 2: تثبيت المقعد) */
    public function stepIndex(): int
    {
        return match ($this->status) {
            self::PAYMENT_SUBMITTED, self::REJECTED, self::WAITLISTED => 1,
            self::CONFIRMED => 2,
            default => 0,
        };
    }

    /** المبلغ المطلوب (المحفوظ وقت التسجيل، أو حسب الساعات للطلبات القديمة) */
    public function amountDue(): int
    {
        return (int) ($this->fee ?: Setting::feeFor((int) $this->hours));
    }

    public function canPay(): bool
    {
        return in_array($this->status, self::AWAITING_PAYMENT, true);
    }

    // ===== Scopes =====

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    // ===== Transitions =====

    public function scheduleInterview(\DateTimeInterface $at, ?string $location): void
    {
        $this->interview_at = $at;
        $this->interview_location = $location;
        if (in_array($this->status, [self::SUBMITTED, self::INTERVIEW, self::WAITLISTED], true)) {
            $this->status = self::INTERVIEW;
        }
        $this->save();
    }

    public function accept(): void
    {
        $this->status = self::ACCEPTED;
        $this->accepted_at = now();
        $this->payment_deadline = now()->addHours(Setting::deadlineHours());
        $this->expired_at = null;
        $this->student_message = null;
        $this->save();
    }

    public function reject(?string $message = null): void
    {
        $this->status = self::REJECTED;
        $this->student_message = $message;
        $this->save();
    }

    public function submitPayment(PaymentMethod $method, ?string $reference, string $receiptPath): void
    {
        if ($this->receipt_path && $this->receipt_path !== $receiptPath) {
            Storage::disk('local')->delete($this->receipt_path);
        }
        $this->payment_method_id = $method->id;
        $this->payment_reference = $reference;
        $this->receipt_path = $receiptPath;
        $this->payment_submitted_at = now();
        $this->status = self::PAYMENT_SUBMITTED;
        $this->student_message = null;
        $this->save();
    }

    public function confirmPayment(): void
    {
        $this->status = self::CONFIRMED;
        $this->confirmed_at = now();
        $this->student_message = null;
        $this->save();
    }

    /** رفض صورة التحويل: الطالب بيرجع يرفعها، مع رسالة ومهلة */
    public function rejectPayment(?string $message, int $hours = 0): void
    {
        $this->status = self::ACCEPTED;
        $this->student_message = $message ?: 'ما قدرنا نتأكد من التحويل، ارفع الصورة مرة تانية.';
        $this->payment_deadline = now()->addHours($hours > 0 ? $hours : Setting::deadlineHours());
        $this->expired_at = null;
        $this->save();
    }

    /** تحويل كل المهل المنتهية إلى "انتهت المهلة" */
    public static function expireOverdue(): int
    {
        return static::query()
            ->where('status', self::ACCEPTED)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<=', now())
            ->update(['status' => self::EXPIRED, 'expired_at' => now(), 'updated_at' => now()]);
    }

    public static function seatsTaken(string $track): int
    {
        return static::query()->where('track', $track)->whereIn('status', self::HOLDS_SEAT)->count();
    }

    public static function hasFreeSeat(string $track): bool
    {
        return static::seatsTaken($track) < (Setting::seats()[$track] ?? 0);
    }

}
