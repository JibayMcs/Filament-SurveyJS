<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;

trait HasSurveyJson
{
    public ?array $surveyJson = [];

    protected ?bool $allFieldsRequired = null;

    protected ?string $locale = null;

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

    public function locale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?? config('survey-js.locale') ?? app()->getLocale();
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

    protected function applyStoreDataAsText(array &$elements): void
    {
        foreach ($elements as &$element) {
            if (isset($element['type']) && in_array($element['type'], ['file', 'signaturepad'])) {
                $element['storeDataAsText'] = false;
            }

            if (isset($element['elements'])) {
                $this->applyStoreDataAsText($element['elements']);
            }
        }
    }

    public function getSurveyTitle(): ?string
    {
        $title = ($this->surveyJson ?? [])['title'] ?? null;

        if (is_array($title)) {
            return $title['default'] ?? reset($title) ?: null;
        }

        return $title;
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
            'readOnly'              => $this->isReadOnly() ?: null,
        ], fn ($value) => $value !== null);

        $json = array_merge($json, $options);

        // Masquer les titres natifs SurveyJS quand contained avec titre (affiché en legend du fieldset)
        if ($this->contained && $this->containedWithTitle) {
            if (! empty($json['title'])) {
                $json['showTitle'] = false;
            } else {
                $json['showPageTitles'] = false;
            }
        }

        if (method_exists($this, 'applySignaturePenColor')) {
            $json = $this->applySignaturePenColor($json);
        }

        if (property_exists($this, 'fileUploadEnabled') && $this->fileUploadEnabled && isset($json['pages'])) {
            foreach ($json['pages'] as &$page) {
                if (isset($page['elements'])) {
                    $this->applyStoreDataAsText($page['elements']);
                }
            }
            unset($page);
        }

        return $json;
    }
}
