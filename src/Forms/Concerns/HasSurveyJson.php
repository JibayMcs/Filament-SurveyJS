<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;
use Illuminate\Contracts\View\View;

trait HasSurveyJson
{
    public ?array $surveyJson = [];

    protected Closure|null $surveyJsonResolver = null;

    protected array $surveyOptions = [];

    protected ?bool $allFieldsRequired = null;

    protected ?string $locale = null;

    public function survey(Closure|string|array|null $json): static
    {
        if ($json instanceof Closure) {
            $this->surveyJsonResolver = $json;
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

    public function completedHtml(string|Closure|View $html): static
    {
        if ($html instanceof Closure || $html instanceof View) {
            $this->surveyOptions['completedHtmlResolver'] = $html;
        } else {
            $this->surveyOptions['completedHtml'] = $html;
        }

        return $this;
    }

    public function option(string $key, mixed $value): static
    {
        $this->surveyOptions[$key] = $value;

        return $this;
    }

    public function options(array $options): static
    {
        $this->surveyOptions = array_merge($this->surveyOptions, $options);

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

    protected function resolveSurveyJson(): array
    {
        if ($this->surveyJsonResolver !== null) {
            $resolved = $this->evaluate($this->surveyJsonResolver);

            if (is_string($resolved)) {
                $resolved = json_decode($resolved, true);
            }

            $this->surveyJson = $resolved ?? [];
            $this->surveyJsonResolver = null;
        }

        return $this->surveyJson ?? [];
    }

    public function getSurveyTitle(): ?string
    {
        $title = $this->resolveSurveyJson()['title'] ?? null;

        if (is_array($title)) {
            return $title['default'] ?? reset($title) ?: null;
        }

        return $title;
    }

    public function getSurveyJson(): array
    {
        $json = $this->resolveSurveyJson();

        if (! empty($this->surveyOptions)) {
            if (isset($this->surveyOptions['completedHtmlResolver'])) {
                $resolver = $this->surveyOptions['completedHtmlResolver'];
                unset($this->surveyOptions['completedHtmlResolver']);

                if ($resolver instanceof Closure) {
                    $resolver = $this->evaluate($resolver);
                }

                if ($resolver instanceof View) {
                    $resolver = $resolver->render();
                }

                $this->surveyOptions['completedHtml'] = $resolver;
            }

            $json = array_merge($json, $this->surveyOptions);
        }

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

        $json = $this->applySignaturePenColor($json);

        if ($this->fileUploadEnabled && isset($json['pages'])) {
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
