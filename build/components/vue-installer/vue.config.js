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
	cpx.copy(cwd + '*.{php,sql}', base);
	cpx.copy(cwd + 'vendor/**/*.*', base + '/vendor');
	cpx.copy(cwd + 'Faker/**/*.*', base + '/Faker');
});

module.exports = {
	outputDir:  base,
	publicPath: ''
}
