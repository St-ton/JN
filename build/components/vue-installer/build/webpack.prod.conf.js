'use strict';
const path                 = require('path'),
      utils                = require('./utils'),
      webpack              = require('webpack'),
      config               = require('../config'),
      merge                = require('webpack-merge'),
      baseWebpackConfig    = require('./webpack.base.conf'),
      CopyWebpackPlugin    = require('copy-webpack-plugin'),
      HtmlWebpackPlugin    = require('html-webpack-plugin'),
      MiniCssExtractPlugin = require('mini-css-extract-plugin'),
      OptimizeCSSPlugin    = require('optimize-css-assets-webpack-plugin'),
      UglifyJsPlugin       = require('uglifyjs-webpack-plugin'),
      env                  = require('../config/prod.env'),
      safeParser           = require('postcss-safe-parser'),
      webpackConfig        = merge(baseWebpackConfig, {
          mode:    'production',
          module:  {
              rules: utils.styleLoaders({
                  sourceMap:  config.build.productionSourceMap,
                  extract:    true,
                  usePostCSS: true
              })
          },
          devtool: config.build.productionSourceMap ? config.build.devtool : false,
          output:  {
              path:     config.build.assetsRoot,
              filename: utils.assetsPath('js/[name].[chunkhash].js')
          },
          plugins: [
              // http://vuejs.github.io/vue-loader/en/workflow/production.html
              new webpack.DefinePlugin({
                  'process.env': env
              }),
              // extract css into its own file
              new MiniCssExtractPlugin({
                  filename: utils.assetsPath('css/[name].[chunkhash].css'),
              }),
              // Compress extracted CSS. We are using this plugin so that possible
              // duplicated CSS from different components can be deduped.
              new OptimizeCSSPlugin({
                  cssProcessorOptions: config.build.productionSourceMap
                                           ? {parser: safeParser, map: {inline: false}}
                                           : {parser: safeParser}
              }),
              // generate dist index.html with correct asset hash for caching.
              // you can customize output by editing /index.html
              // see https://github.com/ampedandwired/html-webpack-plugin
              new HtmlWebpackPlugin({
                  filename:       config.build.index,
                  template:       'index.html',
                  inject:         true,
                  minify:         {
                      removeComments:        true,
                      collapseWhitespace:    true,
                      removeAttributeQuotes: true
                      // more options:
                      // https://github.com/kangax/html-minifier#options-quick-reference
                  },
                  // necessary to consistently work with multiple chunks
                  chunksSortMode: 'dependency'
              }),
              // keep module.id stable when vendor modules does not change
              new webpack.NamedChunksPlugin(),
              new webpack.HashedModuleIdsPlugin(),
              // copy custom static assets
              new CopyWebpackPlugin([
                  {
                      from:   path.resolve(__dirname, '../static'),
                      to:     config.build.assetsSubDirectory,
                      ignore: ['.*']
                  }
              ]),
              // only load DE locale from moment.js
              new webpack.ContextReplacementPlugin(/moment[\/\\]locale$/, /(en|de)$/),
          ],
          optimization: {
              concatenateModules: true,
              splitChunks: {
                  chunks: 'all',
                  cacheGroups: {
                      vendor: {
                          name: 'vendor',
                          test: /[\\/]node_modules[\\/]/,
                          enforce: true,
                      },
                  },
              },
              runtimeChunk: 'single',
              minimizer: [
                  new UglifyJsPlugin({
                      uglifyOptions: {
                          compress: {
                              warnings: false
                          }
                      },
                      sourceMap: config.build.productionSourceMap,
                      parallel: true
                  }),
              ],
          }
      });

if (config.build.productionGzip) {
    const CompressionWebpackPlugin = require('compression-webpack-plugin');

    webpackConfig.plugins.push(
        new CompressionWebpackPlugin({
            asset:     '[path].gz[query]',
            algorithm: 'gzip',
            test:      new RegExp(
                '\\.(' +
                config.build.productionGzipExtensions.join('|') +
                ')$'
            ),
            threshold: 10240,
            minRatio:  0.8
        })
    );
}

if (config.build.bundleAnalyzerReport) {
    const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
    webpackConfig.plugins.push(new BundleAnalyzerPlugin());
}

module.exports = webpackConfig;
