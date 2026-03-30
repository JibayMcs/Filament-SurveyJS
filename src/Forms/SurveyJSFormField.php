<?php

namespace JibayMcs\SurveyJs\Forms;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use JibayMcs\SurveyJs\Enums\CheckErrorsMode;
use JibayMcs\SurveyJs\Enums\ProgressBarLocation;
use JibayMcs\SurveyJs\Models\SurveyJsVersion;

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
    protected ?Closure $onCompleteCallback = null;
    protected ?Notification $completeNotification = null;
    protected ?bool $autoSaveEnabled = null;
    protected ?bool $versioningEnabled = null;
    protected bool $versionOnEveryChange = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateUpdated(function (?array $state, SurveyJSFormField $component): void {
            $isComplete = ! empty($state['__surveyCompleted']);
            $cleanData = collect($state ?? [])->except('__surveyCompleted')->all();

            if ($isComplete) {
                $component->state($cleanData);

                if ($component->onCompleteCallback !== null) {
                    $component->evaluate($component->onCompleteCallback, [
                        'data' => $cleanData,
                        'record' => $component->getRecord(),
                    ]);
                }

                $component->completeNotification?->send();
            }

            if ($component->autoSaveEnabled && $component->getRecord()?->exists) {
                $component->getRecord()->update([
                    $component->getName() => $cleanData,
                ]);
            }

            $record = $component->getRecord();

            if ($component->versioningEnabled && $record?->exists) {
                $shouldVersion = $isComplete || $component->versionOnEveryChange;

                if ($shouldVersion) {
                    SurveyJsVersion::create([
                        'versionable_type' => get_class($record),
                        'versionable_id' => $record->getKey(),
                        'field_name' => $component->getName(),
                        'data' => $cleanData,
                        'completed' => $isComplete,
                    ]);
                }
            }
        });
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

    public function onComplete(Closure $callback): static
    {
        $this->onCompleteCallback = $callback;

        return $this;
    }

    public function completeNotification(Notification $notification): static
    {
        $this->completeNotification = $notification;

        return $this;
    }

    public function autoSave(?bool $condition = true): static
    {
        $this->autoSaveEnabled = $condition;

        if ($condition) {
            $this->live(debounce: 500);
        }

        return $this;
    }

    public function versioning(?bool $condition = true, bool $onEveryChange = false): static
    {
        $this->versioningEnabled = $condition;
        $this->versionOnEveryChange = $onEveryChange;

        if ($onEveryChange) {
            $this->live(debounce: 500);
        }

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
