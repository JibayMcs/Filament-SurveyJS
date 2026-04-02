<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

trait HasSurveySignature
{
    protected string|array|null $signaturePenColor = 'black';

    public function signaturePenColor(string|array $color = 'primary'): static
    {
        $this->signaturePenColor = $color;

        return $this;
    }

    protected function applySignaturePenColor(array $json): array
    {
        $color = $this->resolveColorHex($this->signaturePenColor);

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

    protected function applyPenColorToElements(array &$elements, string $color): void
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
