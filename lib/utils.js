/**
 * Counds the amount of members that the given object has.
 *
 * @param obj The object to count the members of.
 * @return Integer
 */
module.exports.countObjectMembers = function(obj) {
    var result = 0, i;

    for (i in obj) {
        result++;
    }

    return result;
};

module.exports.getFilesFromDirectory = function(dirPath) {
    var i, result = [], stats, filePath, fileNames = require('fs').readdirSync(dirPath);

    for (i = 0; i < fileNames.length; ++i) {
        filePath = require('path').resolve(dirPath + '/' + fileNames[i]);
        stats = require('fs').statSync(filePath);
        if (stats.isFile()) {
            result.push(filePath);
        }
    }

    return result;
};

module.exports.shellExecuteArray = function(options) {
    if (!!process.platform.match(/^win/)) {
        options.file = options.file || 'resolver-tmp.bat';
    } else {
        options.file = options.file || 'resolver-tmp.sh';
    }

    require('fs').writeFileSync(options.file, options.content.join('\r\n'), {
        encoding: 'utf8',
        mode: 0777
    });

    require('child_process').execFile(options.file, [], {}, function(error, stdout, stderr) {
        if (!options.keepFile) {
            require('fs').unlinkSync(options.file);
        }

        if (options.callback) {
            options.callback(error, stdout, stderr);
        }
    });
};

module.exports.httpGetRequest = function(options, httpCallback) {
    var body = '', request;

    request = require('http').request(options, function(res) {
        res.setEncoding('utf8');
        res.on('data', function (chunk) {
            body += chunk;
        });
        res.on('end', function() {
            httpCallback(body);
        });
    });

    request.on('error', function(e) {
        logger.error('Problem with request: ' + e.message);
    });

    request.end();
};

module.exports.checkPermission = function (stats, mask){
    return !!(mask & parseInt((stats.mode & parseInt("777", 8)).toString(8)[0]));
};

module.exports.rmdir = function(path) {
    var fs = require('fs');

    if (fs.existsSync(path)) {
        fs.readdirSync(path).forEach(function(file, index) {
            var curPath = path + "/" + file;
            var stats = fs.statSync(curPath);
            if (stats.isDirectory()) {
                module.exports.rmdir(curPath);
            } else {
                fs.chmodSync(curPath, 0777);
                try {
                    fs.unlinkSync(curPath);
                } catch (e) {
                    require('./logger').log(e);
                }
            }
        });

        try {
            fs.rmdirSync(path);
        } catch (e) {
            require('./logger').log(e);
        }
    }
};
