'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var sourcemaps = require('gulp-sourcemaps');

function buildButtonsCSS() {
  const stylesPath = './js/plugins/osu_buttons/styles'
  return gulp.src(`${stylesPath}/osu_buttons.scss`)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(stylesPath));
}

function buildIconsCSS() {
  const stylesPath = './js/plugins/osu_icons/styles'
  return gulp.src(`${stylesPath}/osu_icons.scss`)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(stylesPath));
}

exports.buildButtonsCSS = buildButtonsCSS;
exports.buildIconsCSS = buildIconsCSS;
exports.default = gulp.series(buildButtonsCSS, buildIconsCSS);
