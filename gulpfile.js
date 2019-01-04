/* globals require, exports */

const gulp = require("gulp");
const parallel = require("gulp").parallel;
const terser = require("gulp-terser");
const csso = require("gulp-csso");
const rename = require("gulp-rename");
const filterStream = require("through2-filter");
const PluginError = require("plugin-error");
const fs = require("fs");
const colors = require("ansi-colors");
const flog = require("fancy-log");
const del = require("del");

const terserOptions = {
    "parse": {},
    "compress": {
        "dead_code": false,
        "unused": false
    },
    "mangle": false,
    "output": {},
    "sourceMap": false,
    "ecma": 6,
    "keep_classnames": false,
    "keep_fnames": false,
    "ie8": false,
    "module": false,
    "nameCache": null,
    "safari10": false,
    "toplevel": false,
    "warnings": false
};

//  options:
//      - includedExtensions (String) [required]
//          Extension to include when detecting cache busted files,
//          should contain the leading "dot".
//      - cacheBustingPart (String) [required]
//          Part of the filename that is used to detect files' duplicates,
//          should contain the leading "dot".
//      - deleteDuplicate (Boolean) [default: false]
//          If a previous duplicate exists, it will be removed.
//      - logChanges (Boolean) [default: false]
//          Fill log all file changes to the STDOUT
//
function pluginHandleCacheBusting (options = {}) {
    const PLUGIN_NAME = "gulp-handle-cachebusting";

    const defaultOpts = {
        includedExtensions: undefined,
        cacheBustingPart: undefined,
        deleteDuplicate: false,
        logChanges: false
    };

    const opts = { ...defaultOpts, ...options };

    const perDirPreviousBustedFilesCache = {};

    const log = function (...args) {
        if (!opts.logChanges) {
            return;
        }

        flog(...args);
    };

    const stream = filterStream.obj(function (file) {
        if (file.isStream()) {
            this.emit("error", new PluginError(PLUGIN_NAME, "Streams are not supported!"));
            return;
        }

        if (!file.isBuffer()) {
            return;
        }

        try {
            const dirname = file.dirname;

            if (!perDirPreviousBustedFilesCache[dirname]) {
                const dirExists = fs.existsSync(dirname);

                if (dirExists) {
                    perDirPreviousBustedFilesCache[dirname] = fs.readdirSync(dirname)
                        .filter(function (filename) {
                            return filename.endsWith(opts.includedExtensions);
                        })
                        .filter(function (filename) {
                            return filename.includes(opts.cacheBustingPart);
                        });
                } else {
                    perDirPreviousBustedFilesCache[dirname] = [];
                }
            }

            const dirFiles = perDirPreviousBustedFilesCache[dirname];

            const dupFilename = dirFiles.find(function (filename) {
                return filename.includes(file.stem);
            });

            if (!dupFilename) {
                log(PLUGIN_NAME + ":", colors.green("✔ ") + file.relative);

                return true;
            }

            const dupFilepath = file.dirname + "/" + dupFilename;
            const dupBuffer = fs.readFileSync(dupFilepath);

            const isExactDuplicate = file.contents.equals(dupBuffer);

            if (!isExactDuplicate) {
                log(PLUGIN_NAME + ":", colors.green("✔ ") + file.relative);
            }

            if (!isExactDuplicate && opts.deleteDuplicate) {
                log(PLUGIN_NAME + ":", colors.green("🗑️ ") + dupFilepath);

                del.sync([ dupFilepath ]);
            }

            return (!isExactDuplicate);
        } catch (err) {
            this.emit("error", new PluginError(PLUGIN_NAME, err));
            return;
        }
    });

    return stream;
}

function taskMinifyJS () {
    return gulp.src([
        "./js/**/**.js",
        "!./js/**/**.min.js"
    ], { base: process.cwd() })
        .pipe(terser(terserOptions))
        .pipe(rename(function (path) {
            path.dirname = "dist/" + path.dirname;
        }))
        .pipe(pluginHandleCacheBusting({
            includedExtensions: ".min.js",
            cacheBustingPart: ".cachebuster-",
            deleteDuplicate: true,
            logChanges: true
        }))
        .pipe(rename({
            extname: (
                ".cachebuster-" + (+(new Date())) + ".min.js"
            )
        }))
        .pipe(gulp.dest("./"));
}

function taskMinifyCSS () {
    const cssoOptions = {
        restructure: false,
        sourceMap: false,
        debug: false
    };

    return gulp.src([
        "./css/**/**.css",
        "!./css/**/**.min.css"
    ], { base: process.cwd() })
        .pipe(csso(cssoOptions))
        .pipe(rename(function (path) {
            path.dirname = "dist/" + path.dirname;
        }))
        .pipe(pluginHandleCacheBusting({
            includedExtensions: ".min.css",
            cacheBustingPart: ".cachebuster-",
            deleteDuplicate: true,
            logChanges: true
        }))
        .pipe(rename({
            extname: (
                ".cachebuster-" + (+(new Date())) + ".min.css"
            )
        }))
        .pipe(gulp.dest("./"));
}

exports.default = parallel(taskMinifyJS, taskMinifyCSS);
