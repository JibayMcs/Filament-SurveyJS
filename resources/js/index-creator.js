import { setLicenseKey } from 'survey-core'
import 'survey-core/survey.i18n'
import 'survey-creator-core/survey-creator-core.i18n'
import { SurveyCreator } from 'survey-creator-js'
import { dark, light } from './filament-theme'

export default function surveyjsCreator({
    state: initialState,
    statePath,
    options,
    locale,
    licenseKey,
}) {
    let creator = null

    function applyTheme(mode) {
        if (!creator) return
        const isDark = mode === 'dark'
        const base = isDark ? dark : light
        const surveyTheme = { ...base, cssVariables: { ...base.cssVariables } }

        // Thème du survey dans le preview/designer
        creator.theme = surveyTheme

        // Thème de l'UI du Creator (sidebar, toolbox, etc.)
        creator.applyCreatorTheme({ isLight: !isDark })
    }

    return {
        state: initialState,
        loading: true,

        init() {
            if (licenseKey) setLicenseKey(licenseKey)

            creator = new SurveyCreator(options)

            if (locale) creator.locale = locale

            // Charger le JSON existant depuis le state Filament
            if (
                this.state &&
                typeof this.state === 'object' &&
                Object.keys(this.state).length > 0
            ) {
                creator.JSON = this.state
            }

            // Sync : quand le Creator sauvegarde, on met à jour le state Livewire
            creator.saveSurveyFunc = (saveNo, callback) => {
                this.state = creator.JSON
                callback(saveNo, true)
            }

            // Sync à chaque modification (pour $entangle temps réel)
            creator.onModified.add(() => {
                this.state = creator.JSON
            })

            applyTheme(Alpine.store('theme'))

            this.$nextTick(() => {
                creator.render(this.$refs.creatorContainer)
                this.loading = false
            })

            // Réagir au changement de thème Filament
            Alpine.effect(() => {
                const mode = Alpine.store('theme')
                this.$nextTick(() => applyTheme(mode))
            })
        },
    }
}
