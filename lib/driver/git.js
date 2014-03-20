function Class() {
    var child_process = require('child_process');
    var logger = require('../logger');

    var handleWin32 = function(dependency, outputPath, exists, callback) {
        var terminal;

        if (exists) {
            terminal = child_process.spawn('cmd', [
                '/C', 'git', 'pull', 'origin', '&&',
                'git', 'reset', '--hard', dependency.source.hash
            ], {
                'cwd': require('path').resolve(outputPath)
            });
        } else {
            terminal = child_process.spawn('cmd', [
                '/C', 'git', 'clone', dependency.package.repository.url, outputPath, '&&',
                'cd', outputPath, '&&',
                'git', 'reset', '--hard', dependency.source.hash
            ], {
                'cwd': process.cwd()
            });
        }

        terminal.stderr.on('data', function (data) {
            logger.error('Error: ' + data);
        });

        terminal.on('exit', function (code) {
            callback(dependency, code === 0);
        });
    };

    this.handle = function(dependency, outputPath, callback) {
        var gitDir = require('path').resolve(outputPath + '/.git');
        var exists = require('fs').existsSync(gitDir);

        if (exists) {
            logger.log('Updating dependency "' + dependency.package.fullname + '"...');
        } else {
            logger.log('Installing dependency "' + dependency.package.fullname + '"...');
        }

        require('mkdirp').mkdirp(outputPath, 0777, function() {
            switch (require('os').platform()) {
                case 'win32':
                    handleWin32(dependency, outputPath, exists, callback);
                    break;
                default:
                    logger.error('Your OS is not supported yet!');
                    return;
            }
        });
    };
}

module.exports.Class = Class;
