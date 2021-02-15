const {watch, series} =require('gulp');

var gulp         = require('gulp'),
    sass         = require('gulp-sass'),
    plumber      = require('gulp-plumber'),
    notify       = require('gulp-notify');
    

var config = {
    src           : './web/css/SASS/*.scss',
    dest          : './web/css/'
};

// Error message
var onError = function (err) {
    notify.onError({
        title   : 'Gulp',
        subtitle: 'Failure!',
        message : 'Error: <%= error.message %>',
        sound   : 'Beep'
    })(err);

    this.emit('end');
};

// Compile CSS
gulp.task('styles', function () {
    var stream = gulp
        .src([config.src])
        .pipe(plumber({errorHandler: onError}))
        .pipe(sass().on('error', sass.logError));

    return stream
        .pipe(gulp.dest('./web/css/'));
});

// added this to avoid having to enter the terminal command every time like some kind of animal
exports.default = watch(config.src, gulp.task('styles'));
