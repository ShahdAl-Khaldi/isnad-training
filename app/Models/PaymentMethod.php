<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['title', 'type', 'account_name', 'account_number', 'iban', 'notes', 'steps', 'active', 'sort'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public const TYPES = [
        'bank' => 'بنك',
        'wallet' => 'محفظة إلكترونية',
    ];

    /** خطوات التحويل كسطور، مع تعبئة {amount} و {number} و {name} */
    public function stepLines(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $this->steps) ?: [];

        return array_values(array_filter(array_map(fn ($l) => trim(preg_replace('/^\s*(\d+[\.\)\-]|[-•*])\s*/u', '', $l)), $lines)));
    }

    /** هوية كل طريقة دفع (لونها وشعارها) حسب اسمها */
    public const BRANDS = [
        'bop' => ['match' => ['فلسطين', 'palestine'], 'logo' => 'images/payments/bank-of-palestine.png', 'label' => 'تحويل بنكي'],
        'jawwal' => ['match' => ['جوال', 'jawwal'], 'logo' => 'images/payments/jawwal-pay.png', 'label' => 'دفع لتاجر', 'qr' => 'images/payments/jawwal-qr.png'],
    ];

    /** باركود الدفع (لجوال باي: دفع لتاجر) */
    public function qrUrl(): ?string
    {
        $qr = self::BRANDS[$this->brand()]['qr'] ?? null;

        return $qr && is_file(public_path($qr)) ? asset($qr) : null;
    }

    public function brand(): ?string
    {
        $title = mb_strtolower((string) $this->title);
        foreach (self::BRANDS as $key => $b) {
            foreach ($b['match'] as $m) {
                if (str_contains($title, $m)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function logoUrl(): ?string
    {
        $b = $this->brand();

        return $b ? asset(self::BRANDS[$b]['logo']) : null;
    }

    public function subtitle(): string
    {
        $b = $this->brand();

        return $b ? self::BRANDS[$b]['label'] : (self::TYPES[$this->type] ?? '');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort')->orderBy('id');
    }
}
