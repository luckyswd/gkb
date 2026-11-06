const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const minifyCSS = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const minify = require("gulp-minify");
const del = require("del");
const include = require('gulp-include')

/* build production */
gulp.task('build', gulp.series(clearDist, gulp.parallel(style, fonts, scripts)));

function style() {
  const number = getRandomInRange(1000, 1000000);
  return gulp.src(
    [
      'assets/style/main.scss',
    ]
  )
    .pipe(sass().on('error', sass.logError))
    .pipe(minifyCSS())
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9'))
    .pipe(concat(`style${number}.min.css`))
    .pipe(gulp.dest('dist/css'));
}

/* Inter берётся из пакета @fontsource/inter, а не хранится в репозитории.
   Нужны только woff2 начертаний 400/500/600/700 в подмножествах
   cyrillic и latin — остальное в сборку не попадает. */
function fonts() {
  return gulp.src(
    'node_modules/@fontsource/inter/files/inter-{cyrillic,latin}-{400,500,600,700}-normal.woff2'
  )
    .pipe(gulp.dest('dist/fonts/Inter'))
}

function scripts() {
  const number = getRandomInRange(1000, 1000000);
  return gulp.src(
    [
      'assets/js/main.js',
    ]
  )
    .pipe(include())
    .on('error', console.log)
    .pipe(concat(`build${number}.js`))
    .pipe(minify({noSource: true}))
    .pipe(gulp.dest('dist/js'))
}

/* build develop */
gulp.task('watch', function () {
  clearDist()
  watchStyles();
  fonts();
  watchScripts()
  gulp.watch([
    'assets/style/**/*',
    'modules/**/*.scss'
  ], watchStyles);
  gulp.watch([
    'assets/style/**/*',
    'modules/**/*.scss'
  ], fonts);
  gulp.watch([
    'assets/js/jquery/*.js',
    'assets/js/lib/*.js',
    'assets/js/partials/*.js',
    'modules/**/*.js'
  ], watchScripts);
})

function watchStyles() {
  return gulp.src(
    [
      'assets/style/main.scss',
    ]
  )
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('dist/css'));
}

function watchScripts() {
  return gulp.src(
    [
      'assets/js/main.js',
    ]
  )
    .pipe(include())
    .on('error', console.log)
    .pipe(concat('build.js'))
    .pipe(gulp.dest('dist/js'))
}

function clearDist(cb) {
  del.sync(['dist/**']);
  if (cb) cb();
}

function getRandomInRange(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}