import * as Survey from 'survey-core'
import 'survey-js-ui'
import { light, dark } from './filament-theme'

export default function surveyjsForm({ state: initialState, surveyJson, panelless, transparent, statePath }) {
    let survey = null
    const UI_KEY = `surveyjs_ui_${statePath}`

    function applyTheme(mode) {
        const base = mode === 'dark' ? dark : light
        const theme = { ...base, cssVariables: { ...base.cssVariables } }
        if (panelless) theme.isPanelless = true
        if (transparent) theme.cssVariables['--sjs-general-backcolor-dim'] = 'transparent'
        survey.applyTheme(theme)
    }

    return {
        state: initialState,
        loading: true,

        init() {
            survey = new Survey.Model(surveyJson)

            // Pré-remplir le survey avec les données existantes depuis le state Filament
            if (this.state && typeof this.state === 'object' && Object.keys(this.state).length > 0) {
                survey.data = this.state
            }

            // Restaurer la position de page depuis le localStorage
            const savedUiState = localStorage.getItem(UI_KEY)
            if (savedUiState) {
                try { survey.uiState = JSON.parse(savedUiState) } catch (_) {}
            }

            applyTheme(Alpine.store('theme'))

            this.$nextTick(() => {
                survey.render(this.$refs.surveyContainer)
                this.loading = false
            })

            // Sync vers le state Livewire à chaque changement de valeur
            survey.onValueChanged.add(() => {
                this.state = survey.data
            })

            // Persister la position de page (page courante, questions ouvertes…)
            survey.onUIStateChanged.add(() => {
                localStorage.setItem(UI_KEY, JSON.stringify(survey.uiState))
            })

            // Complétion : sync final avec marqueur, nettoyage localStorage
            // __surveyCompleted déclenche afterStateUpdated côté PHP
            survey.onComplete.add(() => {
                this.state = { ...survey.data, __surveyCompleted: true }
                localStorage.removeItem(UI_KEY)
            })

            Alpine.effect(() => {
                const mode = Alpine.store('theme')
                this.$nextTick(() => {
                    if (!survey) return
                    applyTheme(mode)
                })
            })
        },
    }
}
