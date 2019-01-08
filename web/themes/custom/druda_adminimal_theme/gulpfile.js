'use strict';

// Load Gulp and tools we will use.
var $ = require('gulp-load-plugins')(),
    del = require('del'),
    extend = require('extend'),
    fs = require('fs'),
    gulp = require('gulp'),
    importOnce = require('node-sass-import-once');

var options = {};

options.gulpWatchOptions = {};

// The root paths are used to construct all the other paths in this
// configuration. The "project" root path is where this gulpfile.js is located.
// While ZURB Foundation distributes this in the theme root folder, you can also
// put this (and the package.json) in your project's root folder and edit the
// paths accordingly.
options.rootPath = {
    project: __dirname + '/',
    theme: __dirname + '/'
};

options.theme = {
    root: options.rootPath.theme,
    scss: options.rootPath.theme + 'scss/',
    css: options.rootPath.theme + 'css/'
};

// Define the node-scss configuration.
options.scss = {
    importer: importOnce,
    outputStyle: 'compressed',
    lintIgnore: ['scss/_settings.scss', 'scss/base/_drupal.scss', 'scss/base/druda_zurb.scss'],
    includePaths: [
        options.rootPath.project + 'node_modules/foundation-sites/scss',
        options.rootPath.project + 'node_modules/motion-ui/src'
    ],
};

// Define which browsers to add vendor prefixes for.
options.autoprefixer = {
    browsers: [
        'last 2 versions',
        'ie >= 9'
    ]
};

// If config.js exists, load that config and overriding the options object.
if (fs.existsSync(options.rootPath.project + '/config.js')) {
    var config = {};
    config = require('./config');
    extend(true, options, config);
}

var scssFiles = [
    options.theme.scss + '**/*.scss',
    // Do not open scss partials as they will be included as needed.
    '!' + options.theme.scss + '**/_*.scss',
];




function lint_sass() {
    return gulp.src(options.theme.scss + '**/*.scss')
    // use gulp-cached to check only modified files.
        .pipe($.sassLint({
            files: {
                include: $.cached('scsslint'),
                ignore: options.scss.lintIgnore
            }
        }))
        .pipe($.sassLint.format());
}

const sass_compile = function () {
    return gulp.src(scssFiles)
        .pipe($.sourcemaps.init())
        // Allow the options object to override the defaults for the task.
        .pipe($.sass(extend(true, {
            noCache: true,
            outputStyle: options.scss.outputStyle,
            sourceMap: true
        }, options.scss)).on('error', $.sass.logError))
        .pipe($.autoprefixer(options.autoprefixer))
        .pipe($.rename({dirname: ''}))
        .pipe($.size({showFiles: true}))
        .pipe($.sourcemaps.write('./'))
        .pipe(gulp.dest(options.theme.css));
};

const clean_css = function () {
    return del([
        options.theme.css + '**/*.css',
        options.theme.css + '**/*.map'
    ], {force: true});
};

function watch_css() {
    return gulp.watch(options.theme.scss + '**/*.scss', options.gulpWatchOptions, sass_compile);
}


// The default task.
// gulp.task('default', gulp.series(sass_compile, drush_cc, lint_sass));


gulp.task('watch', gulp.series(sass_compile, watch_css));


// Build CSS for development environment.
// gulp.task('sass_compile', gulp.series(clean_css, sass_compile));


