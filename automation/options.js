const production = (function() {
	var isProd = false;
	process.argv.forEach(function(val, index, array) {
		if (val == '--production') {
			isProd = true;
		}
	});
	return isProd;
})();

// Set options
const options = {
	cssLang: 'scss', // only SCSS supported currently.
	browserSync: true, // Enable browsersync proxy server?
	production: production,
	development: !production
};

module.exports = options;
