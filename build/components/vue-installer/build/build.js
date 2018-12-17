'use strict';

require('./check-versions')();

process.env.NODE_ENV = 'production';

const ora           = require('ora'),
      rm            = require('rimraf'),
      path          = require('path'),
      chalk         = require('chalk'),
      webpack       = require('webpack'),
      config        = require('../config'),
      webpackConfig = require('./webpack.prod.conf'),
      cpx           = require('cpx'),
      spinner       = ora('building for production...');
spinner.start();

rm(path.join(config.build.assetsRoot, config.build.assetsSubDirectory), err => {
    if (err) {
        throw err;
    }
    cpx.copy(path.resolve(__dirname, '../') + "/.htaccess", config.build.assetsRoot);
    cpx.copy(path.resolve(__dirname, '../') + "/*.{php,sql}", config.build.assetsRoot);
    cpx.copy(path.resolve(__dirname, '../vendor') + "/**/*.*", config.build.assetsRoot + '/vendor');
    cpx.copy(path.resolve(__dirname, '../Faker') + "/**/*.*", config.build.assetsRoot + '/Faker');
    webpack(webpackConfig, (err, stats) => {
        spinner.stop();
        if (err) {
            throw err;
        }
        process.stdout.write(stats.toString({
            colors:       true,
            modules:      false,
            children:     false, // If you are using ts-loader, setting this to true will make TypeScript errors show up during build.
            chunks:       false,
            chunkModules: false
        }) + '\n\n');

        if (stats.hasErrors()) {
            console.log(chalk.red('  Build failed with errors.\n'));
            process.exit(1);
        }

        console.log(chalk.cyan('  Build complete.\n'));
        console.log(chalk.yellow(
            '  Tip: built files are meant to be served over an HTTP server.\n' +
            '  Opening index.html over file:// won\'t work.\n'
        ));
    })
});
