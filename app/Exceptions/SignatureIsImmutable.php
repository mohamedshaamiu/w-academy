<?php

namespace App\Exceptions;

use LogicException;

/**
 * Raised when code attempts to modify a signed agreement (SPEC.md §8.3).
 *
 * This signals a programming error, not a user error: no route, form or
 * service exposes a way for a person to trigger it. It is therefore
 * deliberately not translated — it is never rendered to a user, unlike the
 * ValidationExceptions raised elsewhere in the service layer.
 */
class SignatureIsImmutable extends LogicException
{
    /**
     * @param  array<int, string>  $columns
     */
    public static function forColumns(array $columns): self
    {
        return new self(sprintf(
            'A signed agreement is immutable (SPEC.md §8.3). Rejected write to: %s. '
            .'Corrections are made by revoking through AgreementService::revoke() and re-signing.',
            implode(', ', $columns)
        ));
    }

    public static function alreadyRevoked(int $id): self
    {
        return new self(sprintf('Agreement signature #%d is already revoked; it cannot be revoked twice.', $id));
    }
}
