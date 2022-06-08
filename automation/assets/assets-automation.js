//Compress images and move them to dist folder
//Copy fonts to dist folder

const gulp = require('gulp'),
	options = require('../options'),
	// JS only modules
	noop = require('gulp-noop'),
	watch = options.development ? require('gulp-debounced-watch') : noop(),
	plumber = require('gulp-plumber'),
	rename = require('gulp-rename');

// Copy images to the "dist" folder
// In production, the images are compressed
function font(file, all) {
	var font =
		all == true
			? [
					process.cwd() + '/web/**/src/fonts/**/*.svg',
					process.cwd() + '/web/**/src/fonts/**/*.eot',
					process.cwd() + '/web/**/src/fonts/**/*.ttf',
					process.cwd() + '/web/**/src/fonts/**/*.woff*'
			  ]
			: file.path;

	return gulp
		.src(font, { base: '.' })
		.pipe(plumber())
		.pipe(
			rename(function(path) {
				let paths = path.dirname.split('src');
				path.dirname = paths[0] + '/' + 'dist' + '/' + paths[1];
			})
		)
		.pipe(
			gulp.dest(function(file) {
				console.log('\x1b[42m%s\x1b[0m', 'Copied: ' + file.path);
				return '.';
			})
		);
}

function image(file, all) {
	var images =
		all == true
			? [
					process.cwd() + '/web/**/src/img/**/*.jp*',
					process.cwd() + '/web/**/src/img/**/*.png',
					process.cwd() + '/web/**/src/img/**/*.svg',
					process.cwd() + '/web/**/src/img/**/*.gif',
					process.cwd() + '/web/**/src/img/**/*.tif',
					process.cwd() + '/web/**/src/img/**/*.bmp'
			  ]
			: file.path;

	return gulp
		.src(images, { base: '.' })
		.pipe(plumber())
		.pipe(
			rename(function(path) {
				let paths = path.dirname.split('src');
				path.dirname = paths[0] + '/' + 'dist' + '/' + paths[1];
			})
		)
		.pipe(
			gulp.dest(function(file) {
				console.log('\x1b[42m%s\x1b[0m', 'Copied: ' + file.path);
				return '.';
			})
		);
}

function copyFonts() {
	console.log(
		'\x1b[42m%s\x1b[0m',
		'* * * * Copying every font file. * * * *'
	);

	font('', true);
}

function copyImages() {
	console.log(
		'\x1b[42m%s\x1b[0m',
		'* * * * Copying every image file. * * * *'
	);

	image('', true);
}

gulp.task('assets', function(done) {
	copyFonts();
	copyImages();

	if (options.development) {
		console.log(
			'\x1b[42m%s\x1b[0m',
			'* * * * Watching assets for changes. * * * *'
		);

		watch(
			[
				process.cwd() + '/web/**/src/fonts/**/*',
				process.cwd() + '/web/**/src/img/**/*'
			],
			{ readDelay: 0 },
			font,
			image
		).on('error', error => console.log(`Watcher error: ${error}`));
	}

	done();
});
