/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../logger');

/**
 * Counds the amount of members that the given object has.
 *
 * @param obj The object to count the members of.
 * @return Integer
 */
var countObjectMembers = function(obj) {
    var result = 0, i;

    for (i in obj) {
        result++;
    }

    return result;
};

/**
 * Does a HTTP request.
 *
 * @param data The data of the request.
 * @param onSuccess The callback method.
 */
var httpGetRequest = function(options, onSuccess) {
    var request = require('http').request(options, function(res) {
        res.setEncoding('utf8');
        res.on('data', function (response) {
            onSuccess(response);
        });
    });
    request.on('error', function(e) {
        logger.error('Problem with request: ' + e.message);
    });
    request.end();
};

/**
 * Loads the remote dependencies.
 *
 * @param config The configuration of the project.
 * @param callback The callback method.
 * @return void
 */
var loadRemoteDependencies = function(config, callback) {
    var name, dependencies = [], totalDependencies, dependencyNumber;
    
    dependencyNumber = 0;
    totalDependencies = countObjectMembers(config.dependencies);
    
    var handleOutcome = function(name, dependency) {
        dependencyNumber++;
        
        if (dependency) {
            dependencies.push(dependency);
            logger.ok('Found dependency ' + dependency.name);
        }
        
        if (dependencyNumber === totalDependencies) {
            callback(dependencies);
        }
    };
    
    // First try to find the dependency in the custom repositories. If it's not found there, we
    // try the repository server.
    for (name in config.dependencies) {
        findLocalDependency(config, name, function(name, dependency) {
            if (!dependency) {
                findRemoteDependency(config, name, function(name, dependency) {
                    handleOutcome(name, dependency);
                });
            } else {
                handleOutcome(name, dependency);
            }
        });
    }
};

var findLocalDependency = function(config, name, callback) {
    if (!config.repositories) {
        callback(name);
        return;
    }
    
    // TODO: Implement the finding of the repositories.
    callback(name);
};

var findRemoteDependency = function(config, name, callback) {
    var i, request = name + '@' + config.dependencies[name];
    httpGetRequest({
        host: 'api.pixelpolishers.com',
        path: '/resolver/rest/package/find.json?' + require('querystring').stringify({
            'query': request
        })
    }, function(response) {
        response = JSON.parse(response);

        if (response.success) {
            callback(name, response.result);
        } else {
            logger.error('Failed to load dependency ' + name);
            for (i = 0; i < response.messages.length; ++i) {
                logger.error('-> ' + response.messages[i]);
            }
            
            callback(name);
        }
    });
};

var containsDependency = function(name, dependencies) {
    var i;

    for (i = 0; i < dependencies.length; ++i) {
        if (dependencies[i].name === name) {
            return true;
        }
    }

    return false;
};

/**
 * Sorts the given dependencies so that they are in order.
 *
 * @param dependencies The dependencies to sort.
 * @return Object
 */
var sortDependencies = function(dependencies) {
    var result = [], name, childs, i;

    for (name in dependencies) {
        childs = sortDependencies(dependencies[name].dependencies)
        for (i = 0; i < childs.length; ++i) {
            if (!containsDependency(childs[i].name, result)) {
                result.push(childs[i]);
            }
        }

        dependencies[name].name = name;
        result.push(dependencies[name]);
    }

    return result;
};

/**
 * Loads the dependencies that are defined for this project.
 *
 * @param config The configuration of the project.
 * @param callback The callback that is called once the dependencies are loaded.
 * @return void
 */
exports.loadDependencies = function(config, callback) {
    if (config.dependencies && countObjectMembers(config.dependencies)) {
        loadRemoteDependencies(config, callback);
    } else {
        logger.error('No dependencies configured.');
		callback();
    }
};