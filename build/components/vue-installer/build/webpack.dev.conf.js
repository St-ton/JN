'use strict';
const utils                = require('./utils'),
      webpack              = require('webpack'),
      config               = require('../config'),
      merge                = require('webpack-merge'),
      path                 = require('path'),
      baseWebpackConfig    = require('./webpack.base.conf'),
      CopyWebpackPlugin    = require('copy-webpack-plugin'),
      HtmlWebpackPlugin    = require('html-webpack-plugin'),
      FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin'),
      portfinder           = require('portfinder'),
      HOST                 = process.env.HOST,
      PORT                 = process.env.PORT && Number(process.env.PORT),
      devWebpackConfig     = merge(baseWebpackConfig, {
          mode: 'development',
          module:  {
              rules: utils.styleLoaders({sourceMap: config.dev.cssSourceMap, usePostCSS: true})
          },
          // cheap-module-eval-source-map is faster for development
          devtool: config.dev.devtool,

          // these devServer options should be customized in /config/index.js
          devServer: {
              clientLogLevel:     'warning',
              historyApiFallback: {
                  rewrites: [
                      {from: /.*/, to: path.posix.join(config.dev.assetsPublicPath, 'index.html')},
                  ],
              },
              hot:                true,
              contentBase:        false, // since we use CopyWebpackPlugin.
              compress:           true,
              host:               HOST || config.dev.host,
              port:               PORT || config.dev.port,
              open:               config.dev.autoOpenBrowser,
              overlay:            config.dev.errorOverlay
                                      ? {warnings: false, errors: true}
                                      : false,
              publicPath:         config.dev.assetsPublicPath,
              proxy:              config.dev.proxyTable,
              quiet:              true, // necessary for FriendlyErrorsPlugin
              watchOptions:       {
                  poll: config.dev.poll,
              }
          },
          plugins:   [
              new webpack.DefinePlugin({
                  'process.env': require('../config/dev.env')
              }),
              new webpack.HotModuleReplacementPlugin(),
              // https://github.com/ampedandwired/html-webpack-plugin
              new HtmlWebpackPlugin({
                  filename: 'index.html',
                  template: 'index.html',
                  inject: true
              }),
              // copy custom static assets
              new CopyWebpackPlugin([
                  {
                      from: path.resolve(__dirname, '../static'),
                      to: config.dev.assetsSubDirectory,
                      ignore: ['.*']
                  }
              ])
          ]
      });

module.exports = new Promise((resolve, reject) => {
    portfinder.basePort = process.env.PORT || config.dev.port;
    portfinder.getPort((err, port) => {
        if (err) {
            reject(err);
        } else {
            // publish the new Port, necessary for e2e tests
            process.env.PORT = port;
            // add port to devServer config
            devWebpackConfig.devServer.port = port;

            // Add FriendlyErrorsPlugin
            devWebpackConfig.plugins.push(new FriendlyErrorsPlugin({
                compilationSuccessInfo: {
                    messages: [`Your application is running here: http://${devWebpackConfig.devServer.host}:${port}`],
                },
                onErrors:               config.dev.notifyOnErrors
                                            ? utils.createNotifierCallback()
                                            : undefined
            }));

            resolve(devWebpackConfig);
        }
    })
});
