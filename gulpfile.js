/* globals require, exports */

const gulp = require("gulp");
const terser = require("gulp-terser");
const rename = require("gulp-rename");

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

function minifyJS () {
    return gulp.src([
        "./js/**/**.js",
        "!./js/**/**.min.js"
    ])
        .pipe(terser(terserOptions))
        .pipe(rename({
            extname: (
                ".cache-" + (+(new Date())) + ".min.js"
            )
        }))
        .pipe(gulp.dest("./js"));
}

function defaultTask (cb) {
    minifyJS();

    cb();
}

exports.default = defaultTask;
