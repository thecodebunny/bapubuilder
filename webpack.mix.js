let mix = require('webpack-mix').mix;

let grapesJSAssetsPath = 'src/Modules/GrapesJS/resources/assets/';
mix.copy(grapesJSAssetsPath + 'images', 'dist/pagebuilder/images')
   .sass(grapesJSAssetsPath + 'sass/app.scss', 'dist/pagebuilder')
   .sass(grapesJSAssetsPath + 'sass/page-injection.scss', 'dist/pagebuilder')
   .js(grapesJSAssetsPath + 'js/app.js', 'dist/pagebuilder')
   .js(grapesJSAssetsPath + 'js/page-injection.js', 'dist/pagebuilder');
