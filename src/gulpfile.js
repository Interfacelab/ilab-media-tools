/**
 * Created by jong on 8/6/15.
 */
var argv         = require('minimist')(process.argv.slice(2));
var gulp=require('gulp');
var autoprefixer = require('gulp-autoprefixer');
var browserSync  = require('browser-sync').create();
var changed      = require('gulp-changed');
var concat       = require('gulp-concat');
var rename       = require('gulp-rename');
var gulpif       = require('gulp-if');
var lazypipe     = require('lazypipe');
var merge        = require('merge-stream');
var minifyCss    = require('gulp-minify-css');
var plumber      = require('gulp-plumber');
var runSequence  = require('run-sequence');
var sass         = require('gulp-sass');
var sourcemaps   = require('gulp-sourcemaps');
var include      = require('gulp-include');
var uglify       = require('gulp-uglify');
var preprocess   = require('gulp-preprocess');

var manifest = require('asset-builder')('manifest.json');

var path = manifest.paths;

var config = manifest.config || {};
var globs = manifest.globs;
var project = manifest.getProjectGlobs();
var enabled = {
    // Disable source maps when `--production`
    maps: !argv.production,
    // Fail styles task on error when `--production`
    failStyleTask: argv.production,
    // Strip debug statments from javascript when `--production`
    stripJSDebug: argv.production,
    uglify: argv.production
};

var preprocessContext={
    environment: (argv.production) ? "production":"dev",
    toolMode: (argv.basic) ? "pro" : "basic"
};

var writeToManifest = function(directory) {
    return lazypipe()
        .pipe(gulp.dest, path.dist + directory)
        .pipe(browserSync.stream, {match: '**/*.{js,css}'})();
};

var jsTasks = function(filename) {
    return lazypipe()
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.init());
        })
        .pipe(preprocess,{ extension: 'js', context: preprocessContext })
        .pipe(include)
        .pipe(concat, filename)
        .pipe(function(){
            return gulpif(enabled.uglify,uglify({
                compress: {
                    'drop_debugger': enabled.stripJSDebug
                }
            }));
        })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.write('.', {
                sourceRoot: 'js/'
            }));
        })();
};

var cssTasks = function(filename) {
    return lazypipe()
        .pipe(function() {
            return gulpif(!enabled.failStyleTask, plumber());
        })
        .pipe(preprocess,{ extension: 'css', context: preprocessContext })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.init());
        })
        .pipe(function() {
            return gulpif('*.scss', sass({
                outputStyle: 'nested', // libsass doesn't support expanded yet
                precision: 10,
                includePaths: ['.'],
                errLogToConsole: !enabled.failStyleTask
            }));
        })
        .pipe(concat, filename)
        .pipe(autoprefixer, {
            browsers: [
                '> 5%'
            ]
        })
        .pipe(function(){
            return gulpif(enabled.uglify,minifyCss({
                advanced: false,
                rebase: false,
                keepSpecialComments: 0
            }));
        })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.write('.', {
                sourceRoot: 'styles/'
            }));
        })();
};

gulp.task('php', function() {
        return gulp.src(['{helpers,classes,views}/**/*.php', '*.php'], {base: '.'})
            .pipe(preprocess({ extension: 'html', context: preprocessContext }))
            .pipe(preprocess({ extension: 'js', context: preprocessContext }))
            .pipe(gulp.dest('..'));
});

gulp.task('tools', function() {
        return gulp.src('tools.json', {base: '.'})
            .pipe(preprocess({ extension: 'js', context: preprocessContext }))
            .pipe(gulp.dest('..'));
});

gulp.task('cropper-js', function() {
        return gulp.src(['vendor/cropper/dist/*.js','vendor/cropper/dist/*.js.map'], {base: '.'})
            .pipe(gulp.dest('../public/js/'));
});

gulp.task('cropper-css', function() {
        return gulp.src(['vendor/cropper/dist/*.css','vendor/cropper/dist/*.css.map'], {base: '.'})
            .pipe(gulp.dest('../public/css/'));
});

gulp.task('css', function() {
    var merged = merge();
    manifest.forEachDependency('css', function(dep) {
        console.log(dep);
        var cssTasksInstance = cssTasks(dep.name);
        if (!enabled.failStyleTask) {
            cssTasksInstance.on('error', function(err) {
                console.error(err.message);
                this.emit('end');
            });
        }
        merged.add(gulp.src(dep.globs, {base: ''})
            .pipe(cssTasksInstance));
    });
    return merged
        .pipe(writeToManifest('css'));
});

gulp.task('js', function() {
    var merged = merge();
    manifest.forEachDependency('js', function(dep) {
        merged.add(
            gulp.src(dep.globs, {base: 'js'})
                .pipe(jsTasks(dep.name))
        );
    });
    return merged
        .pipe(writeToManifest('js'));
});

gulp.task('watch', function() {
    browserSync.init({
        files: [
            '../{helpers,classes,views}/**/*.php', 
            '../*.php', 
            '../tools.json', 
            '../css/**/*.css', 
            '../css/*.css', 
            '../js/*.js', 
            'manifest.json',
            '../js/vendor/cropper/dist/*.js', 
            '../css/vendor/cropper/dist/*.css'
        ],
        proxy: config.devUrl,
        host: "192.168.1.8",
	open:"external",
        middleware: function (req, res, next) {
            res.setHeader('Access-Control-Allow-Origin', '*');
            next();
        }
    });
    
    gulp.watch([path.source + '{helpers,classes,views}/**/*.php', path.source + '*.php'], ['php'])
    gulp.watch([path.source + 'tools.json'], ['tools'])
    gulp.watch([path.source + 'styles/**/*.scss', path.source + '*.scss'], ['css']);
    gulp.watch([path.source + 'js/**/*.js', path.source + 'js/*.js'], ['js']);
    gulp.watch([path.source + 'vendor/cropper/dist/*.js'], ['cropper-js']);
    gulp.watch([path.source + 'vendor/cropper/dist/*.css'], ['cropper-css']);
    gulp.watch(['bower.json', 'manifest.json'], ['build']);
});

gulp.task('build', function(callback) {
    runSequence('css',
        'js',
        'php',
        'tools',
        'cropper-css',
        'cropper-js',
        callback);
});

gulp.task('default', ['clean'], function() {
    gulp.start('build');
});


