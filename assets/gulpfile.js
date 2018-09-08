// ## Globals
var argv         = require('minimist')(process.argv.slice(2));
var autoprefixer = require('gulp-autoprefixer');
var browserSync  = require('browser-sync').create();
var concat       = require('gulp-concat');
var changed      = require('gulp-changed');
var flatten      = require('gulp-flatten');
var gulp         = require('gulp');
var gulpif       = require('gulp-if');
var imagemin     = require('gulp-imagemin');
var lazypipe     = require('lazypipe');
var merge        = require('merge-stream');
var cleanCSS     = require('gulp-clean-css');
var plumber      = require('gulp-plumber');
var runSequence  = require('run-sequence');
var sass         = require('gulp-sass');
var sourcemaps   = require('gulp-sourcemaps');
var uglify       = require('gulp-uglify-es').default;
var babel 		 = require('gulp-babel');
var browserify   = require('gulp-browserify');
var webpack 	 = require('webpack-stream');
var notify       = require('node-notifier');
var manifest = require('asset-builder')('./manifest.json');

var path = manifest.paths;
var config = manifest.config || {};
var globs = manifest.globs;
var project = manifest.getProjectGlobs();

var enabled = {
    minify: argv.production,
    // Disable source maps when `--production`
    maps: !argv.production,
    // Fail styles task on error when `--production`
    failStyleTask: argv.production,
    // Fail due to JSHint warnings only when `--production`
    failJSHint: argv.production,
    // Strip debug statments from javascript when `--production`
    stripJSDebug: argv.production
};

var writeToManifest = function(directory) {
    return lazypipe()
        .pipe(gulp.dest, path.dist + directory)
        .pipe(browserSync.stream, {match: '**/*.{js,css}'})
        .pipe(gulp.dest, path.dist + directory)();
};

var cssTasks = function(filename) {
    return lazypipe()
        .pipe(function() {
            return gulpif(!enabled.failStyleTask, plumber());
        })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.init());
        })
        .pipe(function() {
            return gulpif('*.scss', sass({
                outputStyle: 'nested', // libsass doesn't support expanded yet
                precision: 10,
                includePaths: ['.'],
                errLogToConsole: false
            }));
        })
        .pipe(concat, filename)
        .pipe(autoprefixer, {
            browsers: [
                'last 2 versions',
                'android 4',
                'opera 12'
            ]
        })
        .pipe(function(){
            return gulpif(enabled.minify, cleanCSS());
        })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.write('.', {
                sourceRoot: 'css/'
            }));
        })();
};

var jsTasks = function(filename) {
    return lazypipe()
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.init());
        })
        // .pipe(babel, {
        //     presets: ['env']
        // })
        // .pipe(webpack)
        .pipe(function () {
            return gulpif(enabled.minify, uglify({
                compress: {
                    'drop_debugger': enabled.stripJSDebug
                }
            }));
        })
        .pipe(concat, filename)
        // .pipe(babelMinify, {
        //   // compress: {
        //   //   'drop_debugger': enabled.stripJSDebug
        //   // }
        // })
        .pipe(function() {
            return gulpif(enabled.maps, sourcemaps.write('.', {
                sourceRoot: '../../assets/js/'
            }));
        })();
};

gulp.task('styles', ['wiredep'], function() {
    var merged = merge();
    manifest.forEachDependency('css', function(dep) {
        var cssTasksInstance = cssTasks(dep.name);
        cssTasksInstance.on('error', function(err) {
            console.error(err.message);

            notify.notify({
                title: 'Styles Compilation Error',
                message: err.message
            });

            this.emit('end');
        });
        merged.add(gulp.src(dep.globs, {base: 'css'})
            .pipe(cssTasksInstance));
    });
    return merged
        .pipe(writeToManifest('css'));
});

gulp.task('scripts', function() {
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

gulp.task('fonts', function() {
    return gulp.src(globs.fonts)
        .pipe(flatten())
        .pipe(gulp.dest(path.dist + 'fonts'))
        .pipe(browserSync.stream());
});

gulp.task('images', function() {
    return gulp.src(globs.images)
        .pipe(imagemin({
            progressive: true,
            interlaced: true,
            svgoPlugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]
        }))
        .pipe(gulp.dest(path.dist + 'img'))
        .pipe(browserSync.stream());
});

gulp.task('clean', require('del').bind(null, [path.dist+'js', path.dist+'css'], {force: true}));

gulp.task('wiredep', function() {
    var wiredep = require('wiredep').stream;
    return gulp.src(project.css)
        .pipe(wiredep())
        .pipe(changed(path.source + 'css', {
            hasChanged: changed.compareSha1Digest
        }))
        .pipe(gulp.dest(path.source + 'css'));
});

gulp.task('watch', function() {
    browserSync.init({
        proxy: config.devUrl,
        ghostMode: false,
        snippetOptions: {
        }
    });
    gulp.watch([path.source + 'css/**/*'], ['styles', 'styles-built-success']);
    gulp.watch([path.source + 'js/**/*'], ['scripts']);
    gulp.watch([path.source + 'fonts/**/*'], ['fonts']);
    gulp.watch([path.source + 'img/**/*'], ['images']);
    gulp.watch(['manifest.json'], ['build']);
});

gulp.task('styles-built-success', function() {
    notify.notify({
        title: 'Styles Compiled',
        message: 'SASS compiled successfully'
    });
});

gulp.task('build', function(callback) {
    runSequence('styles',
        'scripts',
        ['fonts', 'images'],
        callback);
});

gulp.task('default', ['clean'], function() {
    gulp.start('build');
});