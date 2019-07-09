let mix = require('laravel-mix');
let webpack = require('webpack');
let LiveReloadPlugin = require('webpack-livereload-plugin');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix
	.sass('src/scss/app.scss', 'dist')
	.sass('src/scss/preload.scss', 'dist')

	.js('src/js/app.js', 'dist')

	.extract([
		'jquery', 'bootstrap', 'popper.js', '@shopify/draggable', 'store', 'chart.js', 'chartjs-color', 'moment'
	])

	.copy('src/images', 'dist/images')
	.copy('src/fonts', 'dist/fonts')

	.autoload({
		'jquery': ['$', 'window.jQuery']
	})

	.options({
		processCssUrls: false,
		postCss: [
			require('postcss-discard-comments')({
				removeAll: true
			})
		],
		uglify: {
			uglifyOptions: {
				comments: false
			},
		}
	})

	.webpackConfig({
		resolve: {
			alias: {
				shop: path.resolve(__dirname, './src')
			}
		},
		plugins: [
			new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /de/)
		]
	})

if(!mix.inProduction()) {
	mix.options({
		sourcemaps: 'source-map',
		uglify: {
			sourceMap: true
		}
	})
	.sourceMaps()

	mix.webpackConfig({
		devtool: "inline-source-map",
		plugins: [
	        new LiveReloadPlugin({
	        	port: 25487
	        })
	    ]
	});
}