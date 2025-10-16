<?php

namespace App\Support\Auth;

/**
 * Centralised ability constants and helpers for composing permission strings.
 */
final class Ability
{
    public const View = 'view';
    public const Update = 'update';
    public const Delete = 'delete';

    /**
     * Build a permission string using the provided ability, optional qualifiers, and resource key.
     */
    public static function resource(string $ability, string $resource, string ...$qualifiers): string
    {
        $abilityPart = $ability;

        if (! empty($qualifiers)) {
            $abilityPart .= '_' . implode('_', $qualifiers);
        }

        return "{$abilityPart}_{$resource}";
    }
}
