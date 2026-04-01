<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Filament\Support\Facades\FilamentColor;
use Throwable;

trait HasSurveySignature
{
    protected string|array|null $signaturePenColor = 'black';

    public function signaturePenColor(string|array $color = 'primary'): static
    {
        $this->signaturePenColor = $color;

        return $this;
    }

    protected function resolveSignaturePenColorHex(): ?string
    {
        if ($this->signaturePenColor === null) {
            return null;
        }

        if ($this->signaturePenColor === 'primary') {
            try {
                $colors = FilamentColor::getColors();
                return $colors['primary'][600] ?? $colors['primary']['600'] ?? '#6d28d9';
            } catch (Throwable) {
                return '#6d28d9';
            }
        }

        if (is_array($this->signaturePenColor)) {
            return $this->signaturePenColor[600]
                ?? $this->signaturePenColor['600']
                ?? $this->signaturePenColor[500]
                ?? $this->signaturePenColor['500']
                ?? array_values($this->signaturePenColor)[0];
        }

        return $this->signaturePenColor;
    }

    protected function applySignaturePenColor(array $json): array
    {
        $color = $this->resolveSignaturePenColorHex();
        if ($color === null) {
            return $json;
        }

        if (isset($json['pages'])) {
            foreach ($json['pages'] as &$page) {
                if (isset($page['elements'])) {
                    $this->applyPenColorToElements($page['elements'], $color);
                }
            }
        }

        return $json;
    }

    private function applyPenColorToElements(array &$elements, string $color): void
    {
        foreach ($elements as &$element) {
            if (($element['type'] ?? '') === 'signaturepad') {
                $element['penColor'] = $color;
            }

            if (isset($element['elements'])) {
                $this->applyPenColorToElements($element['elements'], $color);
            }
        }
    }
}
