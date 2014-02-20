/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../logger');

/**
 * The DependencyFinder will request dependency information from external repositories.
 *
 * @class DependencyFinder
 * @constructor
 * @param {Array} repositories The repositories to look up.
 */
function Class(repositories) {
    var httpGetRequest = function(options, onSuccess) {
        var body = '', request;

        request = require('http').request(options, function(res) {
            var contentLength = parseInt(res.headers['content-length']);

            res.setEncoding('utf8');
            res.on('data', function (chunk) {
                body += chunk;

                if (body.length === contentLength) {
                    onSuccess(body);
                }
            });
        });

        request.on('error', function(e) {
            logger.error('Problem with request: ' + e.message);
        });

        request.end();
    };

    var loadRepository = function(host, path, callback) {
        httpGetRequest({
            'host': host,
            'path': path
        }, function(response) {
            var repository = null;

            try {
                var repository = JSON.parse(response);
                repository.api.host = host;
            } catch (e) {
                logger.error('Failed to connect to ' + host + path);
            }

            callback(repository);
        });
    };

    this.loadDefaultRepository = function(callback) {
        var host = 'api.pixelpolishers.com';
        var path = '/resolver/repository.json';

        loadRepository(host, path, callback);
    };

    this.loadDependencies = function(dependencies, callback) {
        var repositoryContainer = [];
        var requestDependencies = function(repository, requestCallback) {
            var request = {};
            for (var i = 0; i < dependencies.length; ++i) {
                request['q[' + i + ']'] = dependencies[i].getName() + '@' + dependencies[i].getVersion();
            }

            httpGetRequest({
                host: repository.api.host,
                path: repository.api.search + '?' + require('querystring').stringify(request)
            }, function(response) {
                requestCallback(JSON.parse(response));
            });
        };

        var downloadDependenciesFromRepo = function() {
            requestDependencies(repositoryContainer[0], function(response) {
                repositoryContainer.shift();
                if (response.status === 200) {
                    for (var i = 0; i < response.packages.length; ++i) {
                    }
                }
                callback(response);
            });
        };

        var totalRepositoriesLoaded = 0, totalRepositoriesToLoad = 1;
        for (var i = 0; i < repositories.length; ++i) {
            if (repositories[i].getType() === 'repository') {
                totalRepositoriesToLoad++;
            }
        }

        var onRepositoryLoaded = function(repository) {
            totalRepositoriesLoaded++;

            if (repository) {
                repositoryContainer.push(repository);
            }

            if (totalRepositoriesLoaded === totalRepositoriesToLoad) {
                downloadDependenciesFromRepo();
            }
        };

        for (var i = 0; i < repositories.length; ++i) {
            switch (repositories[i].getType()) {
                case 'repository':
                    var host = repositories[i].getParam('host');
                    var path = repositories[i].getParam('path');

                    loadRepository(host, path, onRepositoryLoaded);
                    break;
            }
        }

        this.loadDefaultRepository(onRepositoryLoaded);
    };
}

module.exports.Class = Class;
