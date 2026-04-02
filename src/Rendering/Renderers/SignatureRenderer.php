<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class SignatureRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return 'signature';
    }

    public function getDisplayValue(): mixed
    {
        return $this->value;
    }

    protected function getExtraData(): array
    {
        $signatureData = null;

        if (is_string($this->value) && $this->value !== '') {
            $signatureData = $this->resolveFileToBase64($this->value);
        }

        return [
            'signatureData' => $signatureData,
            'width' => $this->question['signatureWidth'] ?? 300,
            'height' => $this->question['signatureHeight'] ?? 200,
        ];
    }
}
