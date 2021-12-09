const path = require('path'),
    rm     = require('rimraf'),
    cpx    = require('cpx'),
    base   = path.resolve(__dirname, '../../../install');

rm(base, err => {
    const cwd = path.resolve('.') + '/';
    if (err) {
        throw err;
    }
    cpx.copy(cwd + '.htaccess', base);
    cpx.copy(cwd + '*.{php,sql,ttf}', base);
    cpx.copy(cwd + 'lib/**/*.*', base + '/lib');
});

module.exports = {
    outputDir:  base,
    publicPath: ''
}
