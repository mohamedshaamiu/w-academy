<?php

namespace App\Concerns;

trait HasTranslatedAttributes
{
    /**
     * Resolve a `{$base}_dv` / `{$base}_en` pair for the active locale,
     * falling back to the other locale when the active one is empty.
     */
    public function translated(string $base): string
    {
        $locale = app()->getLocale();
        $fallback = $locale === 'dv' ? 'en' : 'dv';

        $primary = $this->{"{$base}_{$locale}"} ?? '';
        if ($primary !== '') {
            return $primary;
        }

        return $this->{"{$base}_{$fallback}"} ?? '';
    }

    /**
     * Whether the active locale's column is empty and the value shown is a
     * fallback from the other language.
     */
    public function isTranslationFallback(string $base): bool
    {
        $locale = app()->getLocale();

        return (($this->{"{$base}_{$locale}"} ?? '') === '')
            && (($this->{"{$base}_".($locale === 'dv' ? 'en' : 'dv')} ?? '') !== '');
    }
}
