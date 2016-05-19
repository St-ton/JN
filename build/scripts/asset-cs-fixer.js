#!/usr/bin/env node

/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

var trycatch = require('trycatch')
    cc = require('config-chain'),
    colors = require('colors'),
    xml2js = require('xml2js'),
    path = require('path'),
    fs = require('fs');

var debug = function() {
    console.error.apply(console, arguments);
};

function beautifyFile(file, beautifier, cb) {
    fs.readFile(file, 'utf8', function(err, data) {
        if (err) throw err;
        fs.writeFile(file, beautifier(data, config), (err) => {
            if (err) throw err;
            return cb(data);
        });
    });
}

function loadTemplateAssets(file, cb) {
    fs.readFile(file, 'utf8', function(err, data) {
        if (err) throw err;
        parser.parseString(data, function(err, result) {
            if (err) throw err;
            var minify = result.Template.Minify;
            var list = function(node) {
                return node.File.map(function(item, i) {
                    return item.$.Path;
                });
            };
            cb({
                css: list(minify.CSS),
                js: list(minify.JS)
            });
        });
    });
}

function run(template) {
    loadTemplateAssets(template, function(assets) {
        var dir = path.dirname(template);
        var beautify = function(list, handler) {
            list.forEach(function(file, index) {
                beautifyFile(path.join(dir, file), handler, function() {
                    debug('    %s'.green, file);
                });
            });
        }
        beautify(assets.js, require('js-beautify'));
        beautify(assets.css, require('js-beautify').css);
    });
}

if (process.argv.length < 3) {
    debug("\nUsage: %s <template>\n".red, path.basename(process.argv[1]));
    return;
}

process.on('uncaughtException', (err) => debug('%s', err.stack));

var configFile = path.join(process.cwd(), '.asset_cs'),
    parser = new xml2js.Parser({
        explicitArray: false
    }),
    config = cc(configFile).snapshot;

if (cc.find(configFile)) {
    debug('Loaded config from "%s"', configFile);
}

run(process.argv[2]);