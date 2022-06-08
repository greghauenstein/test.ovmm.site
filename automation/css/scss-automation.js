// Module load
const path = require('path'),
	gulp = require('gulp'),
	options = require('../options'),
	// CSS only modules
	noop = require('gulp-noop'),
	watch = options.development ? require('gulp-debounced-watch') : noop(),
	sourcemaps = options.development ? require('gulp-sourcemaps') : noop(),
	plumber = require('gulp-plumber'),
	rename = require('gulp-rename'),
	cssnano = require('cssnano'),
	postcss = require('gulp-postcss'),
	purgecss = require('@fullhuman/postcss-purgecss'),
	autoprefixer = require('autoprefixer'),
	sassGlob = require('gulp-sass-glob'),
	sass = require('gulp-sass'),
	syntax = require('postcss-scss');

var plugins = [
	autoprefixer(),
	cssnano({
		zindex: false,
		preset: 'default',
		reduceIdents: false,
		discardComments: false
	})
];

if (!options.development) {
	plugins.unshift(
		purgecss({
			content: [
				process.cwd() + '/web/app/themes/**/*.js',
				process.cwd() + '/web/app/themes/**/*.html',
				process.cwd() + '/web/app/themes/**/*.php',
				process.cwd() + '/web/app/themes/**/*.twig',
				process.cwd() + '/web/app/themes/**/*.vue'
			],
			rejected: true,
			whitelistPatterns: [/^slick/, /^js/, /^mce/, /^page_/, /^tns/]
		})
	);
}

function compileFile(file, all) {
	var fileToCompile =
		all == true
			? [
					process.cwd() + '/web/**/src/styles/components/**/*.scss',
					process.cwd() + '/web/**/src/styles/editor-style.scss',
					process.cwd() + '/web/**/src/styles/main.scss',
					'!' + process.cwd() + '/web/**/src/styles/**/_*'
			  ]
			: file.path;

	if (file.path !== undefined) {
		if (
			file.path.indexOf('components') == -1 ||
			file.path.indexOf('_') != -1
		) {
			// If we changed anything other than a component then recompile everything.
			fileToCompile = [
				process.cwd() + '/web/**/src/styles/components/**/*.scss',
				process.cwd() + '/web/**/src/styles/editor-style.scss',
				process.cwd() + '/web/**/src/styles/main.scss',
				'!' + process.cwd() + '/web/**/src/styles/**/_*'
			];
		}
	}

	// If its a component lets compile it standalone.
	gulp.src(fileToCompile, { base: '.' })
		// Error reporting
		.pipe(plumber())
		// Initialize sourcemaps for easier debugging
		.pipe(options.development ? sourcemaps.init() : noop())
		// Compile sass
		.pipe(sassGlob())
		.pipe(
			sass({
				includePaths: ['./node_modules']
			}).on('error', sass.logError)
		)
		//Pass Sass through PostCSS plugins for autoprefixer & cssnano
		.pipe(postcss(plugins, { syntax: syntax }))
		// Set save out location correctly so that we can save to 'compiled' folder.
		.pipe(
			rename(function(path) {
				let paths = path.dirname.split('src');
				path.dirname = paths[0] + '/' + 'dist' + '/' + paths[1];
			})
		)

		// Write out sourcemaps
		.pipe(options.development ? sourcemaps.write('.') : noop())
		// Write out file
		.pipe(
			gulp.dest(function(file) {
				// Compiled notification
				console.log('\x1b[42m%s\x1b[0m', 'Compiled: ' + file.path);
				return '.';
			})
		);
}

function compileAll() {
	console.log(
		'\x1b[42m%s\x1b[0m',
		'* * * * Recompiling every SCSS file. * * * *'
	);

	compileFile('', true);
}

gulp.task('scss', function(done) {
	compileAll();

	if (options.development) {
		console.log(
			'\x1b[42m%s\x1b[0m',
			'* * * * Watching .scss files for changes. * * * *'
		);

		watch(
			[
				process.cwd() + '/web/**/src/styles/**/*.scss',
				process.cwd() + '/web/**/src/styles/*.scss'
			],
			{ readDelay: 0 },
			compileFile
		).on('error', error => console.log(`Watcher error: ${error}`));
	}

	done();
});
