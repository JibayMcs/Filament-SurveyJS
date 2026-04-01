import esbuild from 'esbuild'
import postcss from 'postcss'
import tailwindcss from '@tailwindcss/postcss'
import fs from 'fs/promises'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    plugins: [{
        name: 'watchPlugin',
        setup: function (build) {
            build.onStart(() => {
                console.log(`Build started at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
            })

            build.onEnd((result) => {
                if (result.errors.length > 0) {
                    console.log(`Build failed at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`, result.errors)
                } else {
                    console.log(`Build finished at ${new Date(Date.now()).toLocaleTimeString()}: ${build.initialOptions.outfile}`)
                }
            })
        }
    }],
}

// JS builds via esbuild
compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/index-form.js'],
    outfile: './resources/dist/survey-js-form.js',
}).then(() => {
    console.log(`Build completed for survey-js-form.js`)
})

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/index-creator.js'],
    outfile: './resources/dist/survey-js-creator.js',
}).then(() => {
    console.log(`Build completed for survey-js-creator.js`)
})

// CSS build via PostCSS + Tailwind (pour supporter @apply comme Filament)
async function compileCss(inputFile, outputFile) {
    console.log(`Build started at ${new Date(Date.now()).toLocaleTimeString()}: ${outputFile}`)

    const css = await fs.readFile(inputFile, 'utf8')
    const result = await postcss([tailwindcss()]).process(css, {
        from: inputFile,
        to: outputFile,
    })

    let output = result.css

    if (!isDev) {
        const minified = await esbuild.transform(output, { loader: 'css', minify: true })
        output = minified.code
    }

    await fs.writeFile(outputFile, output)
    console.log(`Build finished at ${new Date(Date.now()).toLocaleTimeString()}: ${outputFile}`)
    console.log(`Build completed for survey.css`)
}

compileCss('./resources/css/index.css', './resources/dist/survey.css')
