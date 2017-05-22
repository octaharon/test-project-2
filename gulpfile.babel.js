'use strict';

import plugins  from 'gulp-load-plugins';
import yargs    from 'yargs';
import gulp     from 'gulp';
import rimraf   from 'rimraf';
import sherpa   from 'style-sherpa';
import yaml     from 'js-yaml';
import buffer       from 'vinyl-buffer';
import fs       from 'fs';
import run      from 'gulp-run';
import chalk    from 'chalk';
import log      from 'gulplog';
import mainBowerFiles from 'main-bower-files';
import merge        from 'merge-stream';

// Load all Gulp plugins into one variable
const $ = plugins();


// Load settings from settings.yml
const {COMPATIBILITY, PATHS} = loadConfig('./config.yml');
const OPTIONS = loadConfig('./config/config.yml');

var PRODUCTION = !OPTIONS.debug || false;
var WATCHER = false;

log.info('Production mode is ' + chalk.yellow(PRODUCTION ? 'ON' : 'OFF'));


function loadConfig(filename) {
    let ymlFile = fs.readFileSync(filename, 'utf8');
    return yaml.load(ymlFile);
}

function runScript(script) {
    return run(script).exec();
}

// Build the "dist" folder by running all of the below tasks
gulp.task('build',
    gulp.series(clean, gulp.parallel(javascript, css, images, assets), styleGuide, rights));

gulp.task('vendor-js', vendorJs);
gulp.task('build-js', javascript);
gulp.task('build-css', css);

gulp.task('watch', watch);

//Install and watch
gulp.task('update', gulp.series(composer, bower, 'build','watch'));

//Install
gulp.task('install', gulp.series(composer, bower, 'build'));

// Build the site, run the server, and watch for file changes
gulp.task('default',
    gulp.series('build', 'watch'));

// Delete the "dist" folder
// This happens every time a build starts
// Then create folders structure
function clean(done) {
    rimraf(PATHS.dist, function () {
        if (!fs.existsSync(PATHS.dist) || !fs.statSync(PATHS.dist).isDirectory())
            fs.mkdirSync(PATHS.dist);

        for (var subfolder in PATHS.assets) {
            fs.mkdirSync(PATHS.dist + '/' + subfolder);
        }
        fs.mkdirSync(PATHS.dist + '/img');
        fs.mkdirSync(PATHS.dist + '/js');
        if (!fs.existsSync('cache'))
            fs.mkdirSync('cache');
        done();
    });
}

function composer() {
    var script = 'composer update';
    if (process.platform == 'win32') {
        script = 'call ' + script;
    }
    return runScript(script);
}

function bower() {
    runScript('bower cache clean');
    return runScript('bower update');
}

function rights(done) {
    if (!/^win/.test(process.platform) && yargs.argv.owner) {
        var cmd = 'sudo chown -R ' + yargs.argv.owner + (yargs.argv.group ? ':' + yargs.argv.group : '') + ' ';
        runScript(cmd + './' + PATHS.dist);
        return runScript(cmd + './cache');
    }
    done();
}

// Copy static files from dependencies and source folders
function assets() {
    var mask;
    var stream = require('merge-stream')();
    for (var subfolder in PATHS.assets) {
        mask = PATHS.assets[subfolder];
        stream.add(gulp.src(mask).pipe(gulp.dest(PATHS.dist + '/' + subfolder)));
    }
    return stream.isEmpty() ? null : stream;
}

// Generate a style guide from the Markdown content and HTML template in styleguide/
function styleGuide(done) {
    sherpa('src/components/styleguide/index.md', {
        output: PATHS.dist + '/templates/styleguide.html',
        template: 'src/components/styleguide/template.html'
    }, done);
}

// Compile Sass and less

function sass(path) {
    return gulp.src(path)
        .pipe($.sourcemaps.init())
        .pipe($.sass({
            includePaths: PATHS.sass.vendor
        })
            .on('error', $.sass.logError))

        .pipe($.concat('app.css'))
        .pipe($.autoprefixer({
            browsers: COMPATIBILITY
        }))
        .pipe($.if(PRODUCTION, $.cssnano()))
        .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
        .pipe(gulp.dest(PATHS.dist + '/css'))
}

function less(path) {
    return gulp.src(path)
        .pipe($.sourcemaps.init())
        .pipe($.less().on('error', function (e) {
            console.log(e);
        }))
        .pipe($.concat('app.css'))
        .pipe($.autoprefixer({
            browsers: COMPATIBILITY
        }))
        .pipe($.if(PRODUCTION, $.cssnano()))
        .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
        .pipe(gulp.dest(PATHS.dist + '/css'));
}

// In production, the CSS is compressed
function css() {
    if (fs.existsSync(PATHS.less.index)) {
        return less(PATHS.less.index);
    }
    if (fs.existsSync(PATHS.sass.index)) {
        return sass(PATHS.sass.index);
    }
    console.log("No scss/less index found");
    return false;
}

// Combine JavaScript into one file
// In production, the file is minified
function javascript() {
    return gulp.src(PATHS.javascript.vendor.concat(PATHS.javascript.project))
        .pipe($.sourcemaps.init())
        .pipe($.if(!WATCHER, $.babel()))
        .pipe($.concat('app.js'))
        .pipe($.if(PRODUCTION, $.uglify({
            "output": {
                ascii_only: true
            },
            "mangle": false,
            "compress": {
                dead_code: false,
                hoist_funs: false
            }
        })))
        .on('error', function (e) {
            console.log(e);
        })
        .pipe($.if(!PRODUCTION, $.sourcemaps.write()))
        .pipe(gulp.dest(PATHS.dist + '/js'));
}

function vendorJs() {
    return gulp.src(mainBowerFiles({
        debugging: !PRODUCTION,
        filter: /\.js$/i
    })).pipe($.concat('vendor.js')).pipe(gulp.dest(PATHS.dist + '/js'));
}

// Copy images to the "dist" folder
// In production, the images are compressed
function images() {
    return gulp.src(PATHS.images)
        .pipe($.if(PRODUCTION, $.imagemin({
            progressive: true
        })))
        .pipe(gulp.dest(PATHS.dist + '/img'));
}


// Watch for changes to static assets, pages, Sass, and JavaScript
function watch() {
    WATCHER = true;
    for (var subfolder in PATHS.assets) {
        gulp.watch(PATHS.assets[subfolder], assets);
    }
    gulp.watch(PATHS.less.watches, css);
    gulp.watch(PATHS.sass.watches, css);
    gulp.watch(PATHS.javascript.project, javascript);
    gulp.watch(PATHS.images, images);
    gulp.watch('src/components/styleguide/**', styleGuide);
}
