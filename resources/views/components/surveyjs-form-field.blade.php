<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-load
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref(id: 'survey-js-styles', package: 'jibaymcs/survey-js'))]"
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc(id: 'survey-js-form', package: 'jibaymcs/survey-js') }}"
        x-data="surveyjsForm({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            surveyJson: @js($field->getSurveyJson()),
            panelless: @js($field->panelless),
            transparent: @js($field->transparent),
        })"
    >
        <template x-if="loading">
            <div class="flex justify-center items-center flex-col">
                <span class="font-semibold text-xl">Chargement</span>
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        </template>

        <div x-show="!loading" x-ref="surveyContainer" x-cloak></div>
    </div>
</x-dynamic-component>
