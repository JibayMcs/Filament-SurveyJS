<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Filament\Support\Facades\FilamentColor;
use Throwable;

trait ResolvesColors
{
    protected function resolveColorHex(string|array|null $color): ?string
    {
        if ($color === null) {
            return null;
        }

        if (is_array($color)) {
            return $color[600]
                ?? $color['600']
                ?? $color[500]
                ?? $color['500']
                ?? array_values($color)[0];
        }

        if (! str_starts_with($color, '#') && ! str_starts_with($color, 'rgb')) {
            try {
                $colors = FilamentColor::getColors();

                if (isset($colors[$color])) {
                    return $colors[$color][600] ?? $colors[$color]['600'] ?? $color;
                }
            } catch (Throwable) {
            }
        }

        return $color;
    }
}
