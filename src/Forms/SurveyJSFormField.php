<?php

namespace JibayMcs\SurveyJs\Forms;

use Closure;
use Filament\Forms\Components\Field;
use JibayMcs\SurveyJs\Enums\CheckErrorsMode;
use JibayMcs\SurveyJs\Enums\ProgressBarLocation;

class SurveyJSFormField extends Field
{
    protected string $view = 'survey-js::components.surveyjs-form-field';
    public ?array $surveyJson = [];
    public ?bool $panelless = false;
    public ?bool $transparent = false;
    protected ?bool $showNavigationButtons = null;
    protected ?bool $showPrevButton = null;
    protected ?bool $showProgressBar = null;
    protected ?ProgressBarLocation $progressBarLocation = null;
    protected ?string $pageNextText = null;
    protected ?string $pagePrevText = null;
    protected ?string $completeText = null;
    protected ?bool $autoAdvanceEnabled = null;
    protected ?bool $autoAdvanceAllowComplete = null;
    protected ?CheckErrorsMode $checkErrorsMode = null;
    protected ?bool $allFieldsRequired = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated()
    }

    public function survey(Closure|string|array|null $json): static
    {
        if (is_callable($json)) {
            $this->surveyJson = $this->evaluate($json);
        } else if (is_string($json)) {
            $this->surveyJson = json_decode($json, true);
        } else {
            $this->surveyJson = $json;
        }

        return $this;
    }

    public function panelless(?bool $condition = true): static
    {
        $this->panelless = $condition;

        return $this;
    }

    public function transparent(?bool $condition = true): static
    {
        $this->transparent = $condition;

        return $this;
    }

    public function showNavigationButtons(?bool $condition = true): static
    {
        $this->showNavigationButtons = $condition;

        return $this;
    }

    public function showPrevButton(?bool $condition = true): static
    {
        $this->showPrevButton = $condition;

        return $this;
    }

    public function showProgressBar(?bool $condition = true): static
    {
        $this->showProgressBar = $condition;

        return $this;
    }

    public function progressBarLocation(ProgressBarLocation $location): static
    {
        $this->progressBarLocation = $location;

        return $this;
    }

    public function pageNextText(string $text): static
    {
        $this->pageNextText = $text;

        return $this;
    }

    public function pagePrevText(string $text): static
    {
        $this->pagePrevText = $text;

        return $this;
    }

    public function completeText(string $text): static
    {
        $this->completeText = $text;

        return $this;
    }

    public function autoAdvance(?bool $condition = true): static
    {
        $this->autoAdvanceEnabled = $condition;
        $this->autoAdvanceAllowComplete = $condition;

        return $this;
    }

    public function checkErrorsMode(CheckErrorsMode $mode): static
    {
        $this->checkErrorsMode = $mode;

        return $this;
    }

    public function allFieldsRequired(?bool $condition = true): static
    {
        $this->allFieldsRequired = $condition;

        return $this;
    }

    private function applyRequiredToElements(array &$elements): void
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
            'showNavigationButtons' => $this->showNavigationButtons,
            'showPrevButton' => $this->showPrevButton,
            'showProgressBar' => $this->showProgressBar,
            'progressBarLocation' => $this->progressBarLocation?->value,
            'pageNextText' => $this->pageNextText,
            'pagePrevText' => $this->pagePrevText,
            'completeText' => $this->completeText,
            'autoAdvanceEnabled' => $this->autoAdvanceEnabled,
            'autoAdvanceAllowComplete' => $this->autoAdvanceAllowComplete,
            'checkErrorsMode' => $this->checkErrorsMode?->value,
        ], fn ($value) => $value !== null);

        return array_merge($json, $options);
    }
}
