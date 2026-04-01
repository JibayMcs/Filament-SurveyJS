<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-load
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref(id: 'survey-js-styles', package: 'jibaymcs/survey-js'))]"
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc(id: 'survey-js-creator', package: 'jibaymcs/survey-js') }}"
        x-data="surveyjsCreator({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            statePath: @js($getStatePath()),
            options: @js($field->getCreatorOptions()),
            toolboxOptions: @js($field->getToolboxOptions()),
            locale: @js($field->getLocale()),
            licenseKey: @js(config('survey-js.license_key')),
        })"
        wire:ignore
    >
        <template x-if="loading">
            <div class="flex justify-center items-center flex-col py-4">
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        </template>

        <div
            x-ref="creatorContainer"
            x-show="!loading"
            x-cloak
        ></div>
    </div>
</x-dynamic-component>
