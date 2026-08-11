<?php

namespace App\Enums;

enum AgreementStatus: string
{
    case Signed = 'signed';
    case Revoked = 'revoked';

    public function label(): string
    {
        return __('agreement.status.'.$this->value);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
