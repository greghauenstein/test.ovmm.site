// Module load
const path = require('path'),
	gulp = require('gulp'),
	options = require('../options'),
	// JS only modules
	compiler = require('webpack'),
	VueLoaderPlugin = require('vue-loader/lib/plugin'),
	noop = require('gulp-noop'),
	watch = options.development ? require('gulp-debounced-watch') : noop(),
	sourcemaps = options.development ? require('gulp-sourcemaps') : noop(),
	plumber = require('gulp-plumber'),
	rename = require('gulp-rename'),
	webpack = require('webpack-stream'),
	named = require('vinyl-named-with-path'),
	through = require('through2');

function compileFile(file, all) {
	var compilePath =
		all == true
			? [
					process.cwd() + '/web/**/src/scripts/**/*.js',
					'!' + process.cwd() + '/web/**/src/scripts/modules/**/*.js',
					'!' + process.cwd() + '/web/**/src/scripts/**/_*.js'
			  ]
			: file.path;

	gulp.src(compilePath, { base: '.' })
		.pipe(plumber())
		.pipe(named())
		.pipe(
			webpack(
				{
					plugins: [new VueLoaderPlugin()],
					module: {
						rules: [
							{ test: /\.css$/, use: 'css-loader' },
							{ test: /\.js$/, use: 'babel-loader' },
							{ test: /\.vue$/, use: 'vue-loader' },
							{
								test: /\.scss$/,
								use: [
									'vue-style-loader',
									'css-loader',
									{
										loader: 'sass-loader',
										options: {
											includePaths: ['./node_modules']
										}
									}
								]
							}
						]
					},
					devtool: options.development ? 'inline-source-map' : false,
					mode: options.development ? 'development' : 'production'
				},
				compiler
			)
		)

		.pipe(
			rename(function(path) {
				let paths = path.dirname.split('src');
				path.dirname = paths[0] + '/' + 'dist' + '/' + paths[1];
			})
		)
		.pipe(
			through.obj(function(file, enc, cb) {
				// Dont pipe through any source map files as it will be handled
				// by gulp-sourcemaps
				const isSourceMap = /\.map$/.test(file.path);
				if (!isSourceMap) this.push(file);
				cb();
			})
		)
		.pipe(
			gulp.dest(function(file) {
				console.log('\x1b[42m%s\x1b[0m', 'Compiled: ' + file.path);
				return '.';
			})
		);
}

function compileAll() {
	console.log(
		'\x1b[42m%s\x1b[0m',
		'* * * * Recompiling every JS file. * * * *'
	);

	compileFile('', true);
}

gulp.task('js', function(done) {
	compileAll();

	if (options.development) {
		console.log(
			'\x1b[42m%s\x1b[0m',
			'* * * * Watching .js files for changes. * * * *'
		);

		// Compile only the file that was changed.
		watch(
			[
				process.cwd() + '/web/**/src/scripts/**/*.js',
				'!' + process.cwd() + '/web/**/src/scripts/**/_*.js'
			],
			{ readDelay: 0 },
			compileFile
		).on('error', error => console.log(`Watcher error: ${error}`));

		// Recompile all files if a .vue SFC, modules (if any were changed we need to recompile all files that use it.)
		watch(
			[
				process.cwd() + '/web/**/src/scripts/**/*.vue',
				process.cwd() + '/web/**/src/scripts/modules/*.js',
				process.cwd() + '/web/**/src/scripts/**/_*.js'
			],
			{ readDelay: 0 },
			compileAll
		).on('error', error => console.log(`Watcher error: ${error}`));
	}

	done();
});
