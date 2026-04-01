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
