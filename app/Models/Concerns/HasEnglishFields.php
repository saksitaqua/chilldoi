<?php

namespace App\Models\Concerns;

trait HasEnglishFields
{
    public function translate(string $field): ?string
    {
        if (app()->getLocale() === 'en') {
            $enValue = $this->{$field.'_en'} ?? null;

            if (filled($enValue)) {
                return $enValue;
            }
        }

        return $this->{$field};
    }
}
