var Encore = require('@symfony/webpack-encore');
//var env = Encore.isDev() ? 'dev' : 'prod';
var env = 'dev';

Encore
    .configureRuntimeEnvironment(env)

    // directory where compiled assets will be stored
    .setOutputPath('./public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('lib', './public/assets/lib.js')
    .addEntry('app', './public/assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(false)
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // enables Sass/SCSS support with modern API
    .enableSassLoader((options) => {
        options.api = 'modern-compiler';
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    .enableVueLoader(() => {}, {
        version: 3,
        // Use full build (includes template compiler) for Symfony/Twig integration
        // We mount Vue to existing server-rendered HTML, so we need the compiler
        runtimeCompilerBuild: true
    })

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

    .autoProvideVariables({
        moment: 'moment',
    })

    .addAliases({
        // No alias needed - Webpack Encore handles Vue build selection
    })
;

module.exports = Encore.getWebpackConfig();
