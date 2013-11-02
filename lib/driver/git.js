/**
 * The child process module that is used to start child processes.
 *
 * @var Object
 */
var child_process = require('child_process');

/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../logger');

var handleWin32 = function(dependency, outputPath, exists) {
    if (exists) {
        process.chdir(outputPath);
        return child_process.spawn('cmd', ['/C', 'git', 'pull', 'origin']);
    } else {
        return child_process.spawn('cmd', ['/C', 'git', 'clone', dependency.location, outputPath]);
    }
};

/**
 * Installs or updates the given dependency.
 *
 * @param dependency The dependency to handle.
 * @param outputPath The directory to store the dependency.
 * @param callback The callback method that is called after installation.
 */
exports.handle = function(dependency, outputPath, callback) {
    var terminal;
    var gitDir = require('path').resolve(outputPath + '/.git');
    var exists = require('fs').existsSync(gitDir);
    
    if (exists) {
        logger.log('Updating ' + dependency.vendor + '/' + dependency.name);
    } else {
        logger.log('Installing ' + dependency.vendor + '/' + dependency.name);
    }
	
	require('mkdirp').mkdirp(outputPath, 0777, function() {
		switch (require('os').platform()) {
			case 'win32':
				terminal = handleWin32(dependency, outputPath, exists);
				break;
			default:
				logger.error('Your OS is not supported yet!');
				return;
		}
		
		terminal.stderr.on('data', function (data) {
			logger.error('Error: ' + data);
		});

		terminal.on('exit', function (code) {
			callback(dependency, code === 0);
		});
	});
};