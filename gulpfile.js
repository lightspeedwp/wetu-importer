const gulp         = require('gulp');
const rtlcss       = require('gulp-rtlcss');
const sass         = require('gulp-sass');
const sourcemaps   = require('gulp-sourcemaps');
const jshint       = require('gulp-jshint');
const concat       = require('gulp-concat');
const uglify       = require('gulp-uglify');
const sort         = require('gulp-sort');
const wppot        = require('gulp-wp-pot');
const gettext      = require('gulp-gettext');
const plumber      = require('gulp-plumber');
const autoprefixer = require('gulp-autoprefixer');
const gutil        = require('gulp-util');
const rename       = require('gulp-rename');
const map          = require('map-stream');
const browserlist  = ['last 2 version', '> 1%'];

const errorreporter = map(function(file, cb) {
	if (file.jshint.success) {
		return cb(null, file);
	}

	console.log('JSHINT fail in', file.path);

	file.jshint.results.forEach(function (result) {
		if (!result.error) {
			return;
		}

		const err = result.error
		console.log(`  line ${err.line}, col ${err.character}, code ${err.code}, ${err.reason}`);
	});

	cb(null, file);
});

gulp.task('default', function() {
	console.log('Use the following commands');
	console.log('--------------------------');
	console.log('gulp compile-js     to compile the js to min.js');
	console.log('gulp watch          to continue watching the files for changes');
	console.log('gulp wordpress-lang to compile the lsx-wetu-importer.pot, en_EN.po and en_EN.mo');
});

gulp.task('sass', function() {
	gulp.src('assets/css/scss/lsx-wetu-importer.scss')
		.pipe(sass())
		.pipe(gulp.dest('assets/css'));
});

gulp.task('compile-css', ['sass']);

gulp.task('js', function() {
	return gulp.src('assets/js/lsx-wetu-importer.js')
		.pipe(plumber({
			errorHandler: function(err) {
				console.log(err);
				this.emit('end');
			}
		}))
		.pipe(jshint())
		//.pipe(errorreporter)
		.pipe(concat('lsx-wetu-importer.min.js'))
		.pipe(uglify())
		.pipe(gulp.dest('assets/js'))
});

gulp.task('compile-js', ['js']);

gulp.task('watch-js', function () {
	return gulp.watch('assets/js/**/*.js', ['compile-js']);
});

gulp.task('watch', ['watch-css', 'watch-js']);

gulp.task('wordpress-pot', function() {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wppot({
			domain: 'lsx-wetu-importer',
			package: 'lsx-wetu-importer',
			team: 'LightSpeed <webmaster@lsdev.biz>'
		}))
		.pipe(gulp.dest('languages/lsx-wetu-importer.pot'))
});

gulp.task('wordpress-po', function() {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wppot({
			domain: 'lsx-wetu-importer',
			package: 'lsx-wetu-importer',
			team: 'LightSpeed <webmaster@lsdev.biz>'
		}))
		.pipe(gulp.dest('languages/en_EN.po'))
});

gulp.task('wordpress-po-mo', ['wordpress-po'], function() {
	return gulp.src('languages/en_EN.po')
		.pipe(gettext())
		.pipe(gulp.dest('languages'))
});

gulp.task('wordpress-lang', (['wordpress-pot', 'wordpress-po-mo']));
