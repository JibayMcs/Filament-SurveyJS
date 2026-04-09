import * as Survey from 'survey-core'
import { setLicenseKey } from 'survey-core'
import 'survey-core/survey.i18n'
import * as SurveyUI from 'survey-js-ui'
import { dark, light } from './filament-theme'

// Enregistrement du composant "barre de progression en pourcentage"
// Compatible survey-js-ui (VanillaJS) via l'API ReactElementFactory
Survey.Serializer.addProperty('survey', 'progressTitle')
const h = SurveyUI.createElement
window.React = window.React || { createElement: h }

class PercentageProgressBar extends SurveyUI.ReactSurveyElement {
    render() {
        const model = this.props.model
        return h(
            'div',
            { className: 'sv-progressbar-percentage' },
            model.progressTitle &&
                h(
                    'div',
                    { className: 'sv-progressbar-percentage__title' },
                    h('span', null, model.progressTitle),
                ),
            h(
                'div',
                { className: 'sv-progressbar-percentage__indicator' },
                model.progressValue > 0 &&
                    h('div', {
                        className: 'sv-progressbar-percentage__value-bar',
                        style: { width: model.progressValue + '%' },
                    }),
            ),
            h(
                'div',
                { className: 'sv-progressbar-percentage__value' },
                h('span', null, model.progressValue + '%'),
            ),
        )
    }
}

SurveyUI.ReactElementFactory.Instance.registerElement(
    'sv-progressbar-percentage',
    (props) => h(PercentageProgressBar, props),
)

export default function surveyjsForm({
    state: initialState,
    surveyJson,
    panelless,
    transparent,
    statePath,
    readOnly,
    locale,
    progressBarPercent,
    contained,
    containedWithTitle,
    fileUploadUrl,
    fileDownloadUrl,
    fileDeleteUrl,
    fileErrors,
    progressBarColor,
    licenseKey,
    customTheme,
    nativeNavigation,
    recordKey,
}) {
    let survey = null
    const UI_KEY = `surveyjs_ui_${statePath}${recordKey ? `_${recordKey}` : ''}`

    function applyTheme(mode) {
        if (customTheme) {
            survey.applyTheme(customTheme)
            return
        }
        const base = mode === 'dark' ? dark : light
        const theme = { ...base, cssVariables: { ...base.cssVariables } }
        if (panelless) theme.isPanelless = true
        if (transparent)
            theme.cssVariables['--sjs-general-backcolor-dim'] = 'transparent'
        survey.applyTheme(theme)
    }

    function updatePageState(component) {
        component.isFirstPage = survey.isFirstPage
        component.isLastPage = survey.isLastPage
        if (containedWithTitle && !survey.title) {
            component.surveyTitle = survey.currentPage?.title || ''
        }
    }

    return {
        state: initialState,
        loading: true,
        isFirstPage: true,
        isLastPage: false,
        isCompleted: false,
        readOnly: readOnly ?? false,
        surveyTitle: '',

        init() {
            if (licenseKey) setLicenseKey(licenseKey)

            survey = new Survey.Model(surveyJson)

            // Appliquer la locale pour les textes d'interface SurveyJS
            if (locale) survey.locale = locale

            // Masquer la navigation native SurveyJS (remplacée par les boutons Filament)
            if (!nativeNavigation) survey.showNavigationButtons = false

            // Barre de progression en pourcentage (layout element custom)
            if (progressBarPercent) {
                survey.addLayoutElement({
                    id: 'progressbar-percentage',
                    component: 'sv-progressbar-percentage',
                    container: 'contentTop',
                    data: survey,
                })
            }

            // Handlers d'upload / download / suppression de fichiers
            if (fileUploadUrl) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content

                survey.onUploadFiles.add((sender, options) => {
                    const formData = new FormData()
                    options.files.forEach((file) =>
                        formData.append('files[]', file),
                    )
                    formData.append('questionType', options.question.getType())

                    fetch(fileUploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            Accept: 'application/json',
                        },
                        body: formData,
                    })
                        .then(async (r) => {
                            if (r.ok) return r.json()
                            if (r.status === 413)
                                throw new Error(
                                    fileErrors?.tooLarge || 'File too large',
                                )
                            if (r.status === 422) {
                                const data = await r.json().catch(() => null)
                                const messages = data?.errors
                                    ? Object.values(data.errors).flat()
                                    : []
                                throw new Error(
                                    messages.join(', ') ||
                                        fileErrors?.failed ||
                                        'Upload failed',
                                )
                            }
                            throw new Error(
                                fileErrors?.failed || 'Upload failed',
                            )
                        })
                        .then((data) => options.callback(data.files))
                        .catch((err) => options.callback([], [err.message]))
                })

                survey.onDownloadFile.add((sender, options) => {
                    // Les données base64 ou data: URLs sont déjà utilisables
                    if (
                        !options.content ||
                        options.content.startsWith('data:')
                    ) {
                        options.callback('success', options.content)
                        return
                    }

                    const url =
                        fileDownloadUrl +
                        (fileDownloadUrl.includes('?') ? '&' : '?') +
                        'path=' +
                        encodeURIComponent(options.content)

                    fetch(url)
                        .then((r) => {
                            if (!r.ok)
                                throw new Error(
                                    `Download failed: ${r.status}`,
                                )
                            return r.blob()
                        })
                        .then(
                            (blob) =>
                                new Promise((resolve) => {
                                    const reader = new FileReader()
                                    reader.onload = () =>
                                        resolve(reader.result)
                                    reader.readAsDataURL(blob)
                                }),
                        )
                        .then((base64) =>
                            options.callback('success', base64),
                        )
                        .catch(() => options.callback('error'))
                })

                survey.onClearFiles.add((sender, options) => {
                    // Déterminer les chemins à supprimer
                    const paths = []
                    if (options.fileName) {
                        // Suppression d'un fichier spécifique
                        const files = Array.isArray(options.value)
                            ? options.value
                            : [options.value]
                        const file = files.find(
                            (f) => f?.name === options.fileName,
                        )
                        if (file?.content) paths.push(file.content)
                    } else if (options.value) {
                        // Suppression de tous les fichiers
                        const files = Array.isArray(options.value)
                            ? options.value
                            : [options.value]
                        files.forEach((f) => {
                            if (f?.content) paths.push(f.content)
                        })
                    }

                    if (paths.length === 0 || !fileDeleteUrl) {
                        options.callback('success')
                        return
                    }

                    Promise.all(
                        paths.map((path) =>
                            fetch(fileDeleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json',
                                    Accept: 'application/json',
                                },
                                body: JSON.stringify({ path }),
                            }),
                        ),
                    )
                        .then(() => options.callback('success'))
                        .catch(() => options.callback('error'))
                })
            }

            // Pré-remplir le survey avec les données existantes depuis le state Filament
            if (
                this.state &&
                typeof this.state === 'object' &&
                Object.keys(this.state).length > 0
            ) {
                survey.data = this.state
            }

            // Restaurer la position de page depuis le localStorage
            const savedUiState = localStorage.getItem(UI_KEY)
            if (savedUiState) {
                try {
                    survey.uiState = JSON.parse(savedUiState)
                } catch (_) {}
            }

            if (containedWithTitle) {
                this.surveyTitle =
                    survey.title || survey.currentPage?.title || ''
            }

            applyTheme(Alpine.store('theme'))

            // Appliquer la couleur de la barre de progression via CSS variable
            if (progressBarColor) {
                this.$el.style.setProperty('--sjs-progress-bar-color', progressBarColor)
            }

            this.$nextTick(() => {
                survey.render(this.$refs.surveyContainer)
                this.loading = false
                updatePageState(this)
            })

            // Sync vers le state Livewire à chaque changement de valeur
            let settingFromSurvey = false
            survey.onValueChanged.add(() => {
                settingFromSurvey = true
                this.state = JSON.parse(JSON.stringify(survey.data))
                this.$nextTick(() => {
                    settingFromSurvey = false
                })
            })

            // Protéger contre les réponses Livewire (entangle) qui écrasent
            // this.state avec des données périmées. survey.data est la source de vérité.
            this.$watch('state', (newState) => {
                if (
                    settingFromSurvey ||
                    !survey ||
                    this.isCompleted ||
                    this.readOnly
                )
                    return
                const surveyData = JSON.parse(JSON.stringify(survey.data))
                if (
                    JSON.stringify(newState) !==
                    JSON.stringify(surveyData)
                ) {
                    settingFromSurvey = true
                    this.state = surveyData
                    this.$nextTick(() => {
                        settingFromSurvey = false
                    })
                }
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
                settingFromSurvey = true
                this.state = {
                    ...JSON.parse(JSON.stringify(survey.data)),
                    __surveyCompleted: true,
                }
                this.isCompleted = true
                localStorage.removeItem(UI_KEY)
                this.$nextTick(() => {
                    settingFromSurvey = false
                })
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
