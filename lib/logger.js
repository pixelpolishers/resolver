/**
 * The commander module used to define the application.
 * https://github.com/visionmedia/commander.js
 *
 * @var Object
 */
var program = require('commander');

/**
 * The colors module used to give color to log messages.
 * https://github.com/marak/colors.js
 * 
 * @type Object
 */
var colors = require('colors');

/**
 * Logs an error message to the output stream.
 *
 * @param msg The message to log.
 * @return void
 */
exports.error = function(msg) {
    this.log(msg.red);
};

/**
 * Logs a warning message to the output stream.
 *
 * @param msg The message to log.
 * @return void
 */
exports.warn = function(msg) {
    this.log(msg.yellow);
};

/**
 * Logs a message to the output stream.
 *
 * @param msg The message to log.
 * @return void
 */
exports.log = function(msg) {
    if (!program.silent) {
        console.log("  " + msg);
    }
};

/**
 * Logs an information message to the output stream.
 *
 * @param msg The message to log.
 * @return void
 */
exports.info = function(msg) {
    this.log(msg.cyan);
};

/**
 * Logs a successful message to the output stream.
 *
 * @param msg The message to log.
 * @return void
 */
exports.ok = function(msg) {
	this.log(msg.green);
};
