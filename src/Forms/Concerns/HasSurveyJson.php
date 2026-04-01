<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;

trait HasSurveyJson
{
    public ?array $surveyJson = [];

    protected ?bool $allFieldsRequired = null;

    public function survey(Closure|string|array|null $json): static
    {
        if (is_callable($json)) {
            $this->surveyJson = $this->evaluate($json);
        } elseif (is_string($json)) {
            $this->surveyJson = json_decode($json, true);
        } else {
            $this->surveyJson = $json;
        }

        return $this;
    }

    public function allFieldsRequired(?bool $condition = true): static
    {
        $this->allFieldsRequired = $condition;

        return $this;
    }

    protected function applyRequiredToElements(array &$elements): void
    {
        foreach ($elements as &$element) {
            $element['isRequired'] = true;

            if (isset($element['elements'])) {
                $this->applyRequiredToElements($element['elements']);
            }
        }
    }

    public function getSurveyJson(): array
    {
        $json = $this->surveyJson ?? [];

        if ($this->allFieldsRequired === true && isset($json['pages'])) {
            foreach ($json['pages'] as &$page) {
                if (isset($page['elements'])) {
                    $this->applyRequiredToElements($page['elements']);
                }
            }
            unset($page);
        }

        $options = array_filter([
            'showNavigationButtons' => $this->showNavigationButtons ?? null,
            'showPrevButton'        => $this->showPrevButton ?? null,
            'showProgressBar'       => $this->progressBarPercent ? false : ($this->showProgressBar ?? null),
            'progressBarType'       => $this->progressBarPercent ? 'questions' : null,
            'progressBarLocation'   => $this->progressBarLocation?->value ?? null,
            'autoAdvanceEnabled'    => $this->autoAdvanceEnabled ?? null,
            'autoAdvanceAllowComplete' => $this->autoAdvanceAllowComplete ?? null,
            'checkErrorsMode'       => $this->checkErrorsMode?->value ?? null,
            'readOnly'              => $this->readOnly ?? null,
        ], fn ($value) => $value !== null);

        $json = array_merge($json, $options);

        if (method_exists($this, 'applySignaturePenColor')) {
            $json = $this->applySignaturePenColor($json);
        }

        return $json;
    }
}
