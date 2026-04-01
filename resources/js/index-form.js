import * as Survey from 'survey-core'
import * as SurveyUI from 'survey-js-ui'
import { light, dark } from './filament-theme'

// Enregistrement du composant "barre de progression en pourcentage"
// Compatible survey-js-ui (VanillaJS) via l'API ReactElementFactory
Survey.Serializer.addProperty('survey', 'progressTitle')
const h = SurveyUI.createElement
window.React = window.React || { createElement: h }

class PercentageProgressBar extends SurveyUI.ReactSurveyElement {
    render() {
        const model = this.props.model
        return h('div', { className: 'sv-progressbar-percentage' },
            model.progressTitle && h('div', { className: 'sv-progressbar-percentage__title' },
                h('span', null, model.progressTitle),
            ),
            h('div', { className: 'sv-progressbar-percentage__indicator' },
                model.progressValue > 0 && h('div', {
                    className: 'sv-progressbar-percentage__value-bar',
                    style: { width: model.progressValue + '%' },
                }),
            ),
            h('div', { className: 'sv-progressbar-percentage__value' },
                h('span', null, model.progressValue + '%'),
            ),
        )
    }
}

SurveyUI.ReactElementFactory.Instance.registerElement(
    'sv-progressbar-percentage',
    (props) => h(PercentageProgressBar, props),
)

export default function surveyjsForm({ state: initialState, surveyJson, panelless, transparent, statePath, readOnly, progressBarPercent }) {
    let survey = null
    const UI_KEY = `surveyjs_ui_${statePath}`

    function applyTheme(mode) {
        const base = mode === 'dark' ? dark : light
        const theme = { ...base, cssVariables: { ...base.cssVariables } }
        if (panelless) theme.isPanelless = true
        if (transparent) theme.cssVariables['--sjs-general-backcolor-dim'] = 'transparent'
        survey.applyTheme(theme)
    }

    function updatePageState(component) {
        component.isFirstPage = survey.isFirstPage
        component.isLastPage = survey.isLastPage
    }

return {
        state: initialState,
        loading: true,
        isFirstPage: true,
        isLastPage: false,
        readOnly: readOnly ?? false,

        init() {
            survey = new Survey.Model(surveyJson)

            // Masquer la navigation native SurveyJS (remplacée par les boutons Filament)
            survey.showNavigationButtons = false

            // Barre de progression en pourcentage (layout element custom)
            if (progressBarPercent) {
                survey.addLayoutElement({
                    id: 'progressbar-percentage',
                    component: 'sv-progressbar-percentage',
                    container: 'contentTop',
                    data: survey,
                })
            }

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
                updatePageState(this)
            })

            // Sync vers le state Livewire à chaque changement de valeur
            survey.onValueChanged.add(() => {
                this.state = survey.data
            })

            // Mise à jour de l'état de navigation au changement de page
            survey.onCurrentPageChanged.add(() => {
                updatePageState(this)
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

        nextPage() {
            survey.nextPage()
        },

        prevPage() {
            survey.prevPage()
        },

        completeSurvey() {
            survey.tryComplete()
        },
    }
}
