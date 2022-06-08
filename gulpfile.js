// We don't want to require any special software to be installed upon
// the developers computer. Everything necessary for development should
// be bundled within this install, composer & capistrano.
const options = require('./automation/options.js');
const gulp = require('gulp');
const timestamps = require('console-stamp')(console, '[HH:MM:ss.l]');
// RUN:
// gulp scss
// gulp js
// gulp lint

const automation = {
	cssLang:
		options.cssLang !== 'none'
			? require('./automation/css/' + options.cssLang + '-automation.js')
			: '',
	js: require('./automation/js/js-automation.js'),
	assets: require('./automation/assets/assets-automation.js'),
	browserSync:
		options.browserSync && options.development
			? require('./automation/tools/browser-sync.js')
			: ''
};
