<?php

namespace App\Enums;

enum Locale: string
{
    case Dhivehi = 'dv';
    case English = 'en';

    public function label(): string
    {
        return __('common.locale.'.$this->value);
    }

    public function direction(): string
    {
        return $this === self::Dhivehi ? 'rtl' : 'ltr';
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
