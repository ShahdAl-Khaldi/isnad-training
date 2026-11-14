<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('isnad.settings', fn () => static::query()->pluck('value', 'key')->all());

        return $all[$key] ?? config("isnad.defaults.$key", $default);
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
        Cache::forget('isnad.settings');
    }

    /** الرسوم لكل خيار ساعات: [72 => 50, 120 => 70] */
    public static function fees(): array
    {
        $saved = json_decode((string) static::get('fees', '{}'), true) ?: [];
        $defaults = config('isnad.hours_fees', []);
        $fees = [];
        foreach (config('isnad.hours_options') as $h) {
            $fees[$h] = (int) ($saved[$h] ?? $defaults[$h] ?? 0);
        }

        return $fees;
    }

    public static function feeFor(int $hours): int
    {
        return static::fees()[$hours] ?? (int) max(static::fees() ?: [0]);
    }

    /** نص قصير للأسعار، مثلاً: "50 أو 70" */
    public static function feesLabel(): string
    {
        return implode(' أو ', array_unique(array_values(static::fees())));
    }

    public static function deadlineHours(): int
    {
        return max(1, (int) static::get('deadline_hours', 48));
    }

    public static function registrationOpen(): bool
    {
        return (string) static::get('registration_open', '1') === '1';
    }

    /** عدد المقاعد لكل مسار */
    public static function seats(): array
    {
        $saved = json_decode((string) static::get('seats', '{}'), true) ?: [];
        $default = (int) config('isnad.defaults.default_seats', 10);
        $seats = [];
        foreach (array_keys(config('isnad.tracks')) as $track) {
            $seats[$track] = (int) ($saved[$track] ?? $default);
        }

        return $seats;
    }
}
