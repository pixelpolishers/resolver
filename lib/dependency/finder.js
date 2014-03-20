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
    var loadRepository = function(host, path, callback) {
        require('./../utils').httpGetRequest({
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

    var loadDefaultRepository = function(callback) {
        var host = 'api.pixelpolishers.com';
        var path = '/resolver/resolver.json';

        loadRepository(host, path, callback);
    };

    var loadRepositories = function(callback) {
        var repositoryContainer = [];
        var totalRepositoriesLoaded = 0;
        var totalRepositoriesToLoad = 1;

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
                callback(repositoryContainer);
            }
        };

        for (var i = 0; i < repositories.length; ++i) {
            switch (repositories[i].getType()) {
                case 'repository':
                    var host = repositories[i].getParam('host');
                    var path = repositories[i].getParam('path');

                    loadRepository(host, path, onRepositoryLoaded);
                    break;
                default:
                    throw 'Unknown repository type: ' + repositories[i].getType();
            }
        }

        loadDefaultRepository(onRepositoryLoaded);
    };

    this.loadDependencies = function(dependencies, callback) {
        var requestDependencies = function(repository, dependenciesToDownload, requestCallback) {
            var request = {};
            for (var i = 0; i < dependenciesToDownload.length; ++i) {
                request['q[' + i + ']'] = dependenciesToDownload[i];
            }

            require('./../utils').httpGetRequest({
                host: repository.api.host,
                path: repository.api.lookup + '?' + require('querystring').stringify(request)
            }, function(response) {
                var json;

                try {
                    json = JSON.parse(response);
                } catch (e) {
                    logger.log(e);
                }

                requestCallback(json);
            });
        };

        var downloadDependenciesFromRepositories = function(repositories, downloadingFinished) {
            var foundDeps = [];
            var dependenciesToDownload = [];
            var activeRepoIndex = -1;
            var iterationCounter = 0;
            var pckIndex;

            var findCallback = function(response) {
                if (response && response.status === 200) {
                    for (var i = 0; i < response.packages.length; ++i) {
                        if (response.packages[i].status === 200) {
                            pckIndex = dependenciesToDownload.indexOf(response.packages[i].query);

                            if (pckIndex !== -1) {
                                dependenciesToDownload.splice(pckIndex, 1);
                            }

                            for (var depIdx = 0; depIdx < response.packages[i].dependencies.length; ++depIdx) {
                                dependenciesToDownload.push(response.packages[i].dependencies[depIdx]);
                            }

                            foundDeps.push(response.packages[i]);
                        }
                    }
                }

                findDependencies();
            };

            var findDependencies = function() {
                if (dependenciesToDownload.length === 0) {
                    downloadingFinished(foundDeps);
                    return;
                }

                if (iterationCounter >= 10) {
                    downloadingFinished(foundDeps);
                    return;
                }

                activeRepoIndex++;

                if (activeRepoIndex === repositories.length) {
                    iterationCounter++;
                    activeRepoIndex = 0;
                }

                requestDependencies(repositories[activeRepoIndex], dependenciesToDownload, findCallback);
            };

            for (var i = 0; i < dependencies.length; ++i) {
                dependenciesToDownload.push(dependencies[i].getName()
                                    + '@' + dependencies[i].getVersion());
            }

            findDependencies();
        };

        loadRepositories(function(repositories) {
            logger.log('');

            if (repositories.length === 0) {
                logger.error('No repositories found, cannot download dependencies.');
                logger.log('');
                callback([]);
            } else {
                logger.log('Found ' + repositories.length + (repositories.length == 1 ? ' repository' : ' repositories') + ':');
                for (var i = 0; i < repositories.length; ++i) {
                    logger.log('  - ' + repositories[i].api.host + repositories[i].api.resolver);
                }

                downloadDependenciesFromRepositories(repositories, function(dependencies) {
                    logger.log('');
                    if (dependencies.length === 0) {
                        logger.log('No dependencies found.');
                    } else {
                        logger.log('Found ' + dependencies.length + (dependencies.length == 1 ? ' dependency' : ' dependencies') + ':');
                    }
                    for (var i = 0; i < dependencies.length; ++i) {
                        logger.log('  - ' + dependencies[i].package.fullname + ' (' + dependencies[i].version + ')');
                    }
                    logger.log('');

                    callback(dependencies);
                });
            }
        });
    };
}

module.exports.Class = Class;
