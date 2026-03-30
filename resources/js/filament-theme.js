const shared = {
    themeName: 'default',
    isPanelless: false,
    cssVariables: {
        // Primary
        '--sjs-primary-backcolor': 'var(--primary-600)',
        '--sjs-primary-backcolor-dark': 'var(--primary-700)',
        '--sjs-primary-backcolor-light': 'var(--primary-50)',
        '--sjs-primary-forecolor': 'var(--color-white, #fff)',
        '--sjs-primary-forecolor-light': 'var(--primary-200)',

        // Secondary (warning)
        '--sjs-secondary-backcolor': 'var(--warning-500)',
        '--sjs-secondary-backcolor-light': 'var(--warning-50)',
        '--sjs-secondary-backcolor-semi-light': 'var(--warning-100)',
        '--sjs-secondary-forecolor': 'var(--color-white, #fff)',
        '--sjs-secondary-forecolor-light': 'var(--warning-200)',

        // Special colors: red (danger)
        '--sjs-special-red': 'var(--danger-600)',
        '--sjs-special-red-light': 'var(--danger-50)',
        '--sjs-special-red-forecolor': 'var(--color-white, #fff)',

        // Special colors: green (success)
        '--sjs-special-green': 'var(--success-600)',
        '--sjs-special-green-light': 'var(--success-50)',
        '--sjs-special-green-forecolor': 'var(--color-white, #fff)',

        // Special colors: blue (info)
        '--sjs-special-blue': 'var(--info-600)',
        '--sjs-special-blue-light': 'var(--info-50)',
        '--sjs-special-blue-forecolor': 'var(--color-white, #fff)',

        // Special colors: yellow (warning)
        '--sjs-special-yellow': 'var(--warning-500)',
        '--sjs-special-yellow-light': 'var(--warning-50)',
        '--sjs-special-yellow-forecolor': 'var(--color-white, #fff)',

        // Typography
        '--sjs-font-family': 'var(--font-sans, ui-sans-serif, system-ui, sans-serif)',
        '--sjs-font-size': '14px',

        // Shape
        '--sjs-corner-radius': '8px',
        '--sjs-base-unit': '8px',
    },
}

export const light = {
    ...shared,
    colorPalette: 'light',
    cssVariables: {
        ...shared.cssVariables,

        // General backgrounds
        '--sjs-general-backcolor': 'var(--color-white, #fff)',
        '--sjs-general-backcolor-dark': 'var(--gray-100)',
        '--sjs-general-backcolor-dim': 'var(--gray-100)',
        '--sjs-general-backcolor-dim-light': 'var(--gray-50)',
        '--sjs-general-backcolor-dim-dark': 'var(--gray-200)',

        // General foregrounds
        '--sjs-general-forecolor': 'var(--gray-950)',
        '--sjs-general-forecolor-light': 'var(--gray-500)',
        '--sjs-general-dim-forecolor': 'var(--gray-900)',
        '--sjs-general-dim-forecolor-light': 'var(--gray-500)',

        // Editor panel (inputs)
        '--sjs-editorpanel-backcolor': 'var(--gray-50)',
        '--sjs-editorpanel-hovercolor': 'var(--gray-100)',

        // Question panel
        '--sjs-questionpanel-backcolor': 'var(--color-white, #fff)',
        '--sjs-questionpanel-hovercolor': 'var(--gray-50)',

        // Borders
        '--sjs-border-default': 'var(--gray-300)',
        '--sjs-border-light': 'var(--gray-200)',
        '--sjs-border-inside': 'var(--gray-200)',

        // Font colors
        '--sjs-font-pagetitle-color': 'var(--gray-950)',
        '--sjs-font-pagedescription-color': 'var(--gray-500)',
        '--sjs-font-questiontitle-color': 'var(--gray-950)',
        '--sjs-font-questiondescription-color': 'var(--gray-500)',
        '--sjs-font-editorfont-color': 'var(--gray-950)',
        '--sjs-font-editorfont-placeholdercolor': 'var(--gray-400)',

        // Shadows
        '--sjs-shadow-small': '0px 1px 2px 0px rgba(0, 0, 0, 0.05)',
        '--sjs-shadow-small-reset': '0px 0px 0px 0px rgba(0, 0, 0, 0)',
        '--sjs-shadow-medium': '0px 1px 3px 0px rgba(0, 0, 0, 0.1), 0px 1px 2px -1px rgba(0, 0, 0, 0.1)',
        '--sjs-shadow-large': '0px 4px 6px -1px rgba(0, 0, 0, 0.1), 0px 2px 4px -2px rgba(0, 0, 0, 0.1)',
        '--sjs-shadow-inner': 'inset 0px 2px 4px 0px rgba(0, 0, 0, 0.05)',
        '--sjs-shadow-inner-reset': 'inset 0px 0px 0px 0px rgba(0, 0, 0, 0)',
    },
}

export const dark = {
    ...shared,
    colorPalette: 'dark',
    cssVariables: {
        ...shared.cssVariables,

        // General backgrounds
        '--sjs-general-backcolor': 'var(--gray-800)',
        '--sjs-general-backcolor-dark': 'var(--gray-700)',
        '--sjs-general-backcolor-dim': 'var(--gray-900)',
        '--sjs-general-backcolor-dim-light': 'var(--gray-800)',
        '--sjs-general-backcolor-dim-dark': 'var(--gray-950)',

        // General foregrounds
        '--sjs-general-forecolor': 'var(--gray-100)',
        '--sjs-general-forecolor-light': 'var(--gray-400)',
        '--sjs-general-dim-forecolor': 'var(--gray-100)',
        '--sjs-general-dim-forecolor-light': 'var(--gray-400)',

        // Editor panel (inputs)
        '--sjs-editorpanel-backcolor': 'var(--gray-900)',
        '--sjs-editorpanel-hovercolor': 'var(--gray-700)',

        // Question panel
        '--sjs-questionpanel-backcolor': 'var(--gray-800)',
        '--sjs-questionpanel-hovercolor': 'var(--gray-700)',

        // Borders
        '--sjs-border-default': 'var(--gray-600)',
        '--sjs-border-light': 'var(--gray-700)',
        '--sjs-border-inside': 'var(--gray-700)',

        // Font colors
        '--sjs-font-pagetitle-color': 'var(--gray-100)',
        '--sjs-font-pagedescription-color': 'var(--gray-400)',
        '--sjs-font-questiontitle-color': 'var(--gray-100)',
        '--sjs-font-questiondescription-color': 'var(--gray-400)',
        '--sjs-font-editorfont-color': 'var(--gray-100)',
        '--sjs-font-editorfont-placeholdercolor': 'var(--gray-500)',

        // Shadows (subtler in dark mode)
        '--sjs-shadow-small': '0px 1px 2px 0px rgba(0, 0, 0, 0.2)',
        '--sjs-shadow-small-reset': '0px 0px 0px 0px rgba(0, 0, 0, 0)',
        '--sjs-shadow-medium': '0px 1px 3px 0px rgba(0, 0, 0, 0.3), 0px 1px 2px -1px rgba(0, 0, 0, 0.3)',
        '--sjs-shadow-large': '0px 4px 6px -1px rgba(0, 0, 0, 0.3), 0px 2px 4px -2px rgba(0, 0, 0, 0.3)',
        '--sjs-shadow-inner': 'inset 0px 2px 4px 0px rgba(0, 0, 0, 0.2)',
        '--sjs-shadow-inner-reset': 'inset 0px 0px 0px 0px rgba(0, 0, 0, 0)',
    },
}
