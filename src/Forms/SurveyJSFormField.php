<?php

namespace JibayMcs\SurveyJs\Forms;

use Filament\Forms\Components\Field;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveyCompletion;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveyDisplay;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveyJson;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveyNavigation;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveyPersistence;
use JibayMcs\SurveyJs\Forms\Concerns\HasSurveySignature;
use JibayMcs\SurveyJs\Models\SurveyJsVersion;

class SurveyJSFormField extends Field
{
    use HasSurveyCompletion;
    use HasSurveyDisplay;
    use HasSurveyJson;
    use HasSurveyNavigation;
    use HasSurveyPersistence;
    use HasSurveySignature;

    protected string $view = 'survey-js::components.surveyjs-form-field';

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelless = config('survey-js.panelless', false);
        $this->transparent = config('survey-js.transparent', false);
        $this->contained = config('survey-js.contained', true);
        $this->containedWithTitle = config('survey-js.contained_with_title', true);
        $this->readOnly = config('survey-js.read_only', false);
        $this->locale = config('survey-js.locale');

        $this->afterStateHydrated(function (SurveyJSFormField $component, $state): void {
            if ($state === null) {
                $component->state([]);
            }
        });

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return collect($state)->except('__surveyCompleted')->all();
            }

            return $state ?? [];
        });

        $this->afterStateUpdated(function (?array $state, SurveyJSFormField $component): void {
            $isComplete = !empty($state['__surveyCompleted']);
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
}
