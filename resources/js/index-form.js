import * as Survey from 'survey-core'
import 'survey-js-ui'
import { light, dark } from './filament-theme'

export default function surveyjsForm({ state, surveyJson, panelless, transparent }) {
    let survey = null

    function applyTheme(mode) {
        const base = mode === 'dark' ? dark : light
        const theme = { ...base, cssVariables: { ...base.cssVariables } }
        if (panelless) theme.isPanelless = true
        if (transparent) theme.cssVariables['--sjs-general-backcolor-dim'] = 'transparent'
        survey.applyTheme(theme)
    }

    return {
        loading: true,

        init() {
            survey = new Survey.Model(surveyJson)

            applyTheme(Alpine.store('theme'))

            this.$nextTick(() => {
                survey.render(this.$refs.surveyContainer)
                this.loading = false
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
