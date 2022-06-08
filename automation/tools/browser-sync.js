// Module load
const gulp = require('gulp'),
	browserSync = require('browser-sync').create(),
	options = require('../options'),
	readline = require('readline'),
	fs = require('fs');

function init() {
	let siteUrl = false;

	// Read the .env file if it exists
	const readInterface = readline.createInterface({
		input: fs.createReadStream('./.env')
	});

	readInterface
		.on('line', function(line) {
			if (line.indexOf('DOMAIN_CURRENT_SITE') != -1) {
				siteUrl = line.split('=')[1];
				readInterface.close();
			}
		})
		.on('close', function() {
			if (siteUrl != false) {
				browserSync.init({
					proxy: siteUrl,
					baseDir: ['./'],
					reloadDebounce: 1000
				});

				browserSync.watch(
					[
						'./**/dist/**/*.js',
						'./**/dist/**/*.css',
						'./**/themes/**/*.html',
						'./**/themes/**/*.jpg',
						'./**/themes/**/*.png',
						'./**/themes/**/*.svg',
						'./**/themes/**/*.gif',
						'./**/themes/**/*.bmp',
						'./**/themes/**/*.php',
						'./**/themes/**/*.twig',
						'!./node_modules/**/*'
					],
					(evt, file) => {
						if (evt === 'change' && file.indexOf('.css') === -1) {
							browserSync.reload();
						}
						if (evt === 'change' && file.indexOf('.css') !== -1) {
							browserSync.reload('*.css');
						}
					}
				);
			} else {
				console.log(
					'Error with BrowserSync! Make sure your .env file is setup in the root!'
				);
			}
		});
}

gulp.task('browsersync', init);
