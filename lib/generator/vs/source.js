/**
 * The file system module used to the config files.
 *
 * @var Object
 */
var fs = require('fs');

/**
 * The path module used to work with paths.
 *
 * @var Object
 */
var path = require('path');

var initializeFilter = function(name, extensions, parent) {
	return {
		'name': name,
		'extensions': extensions || [],
		'parent': parent,
		'source': {
			'compile': [],
			'include': [],
			'ignore': [],
			'resource': []
		},
		'filters': []
	};
};

var findFilter = function(filter, name) {
	var i;

    if (filter) {
        for (i = 0; i < filter.filters.length; ++i) {
            if (filter.filters[i].name === name) {
                return filter.filters[i];
            }
        }
    }
};

var createFilter = function(name, extensions, parent) {
    var i, names = name.split('/'), filter, parentFilter = parent;

    for (i = 0; i < names.length; ++i) {
        filter = findFilter(parentFilter, names[i]);
        if (!filter) {
            filter = initializeFilter(names[i], extensions, parentFilter);
            if (parentFilter) {
                parentFilter.filters.push(filter);
            }
        }
        parentFilter = filter;
    }

    return filter;
};

var readDirRecursiveSync = function(dir, recursive) {
	var files = [], list, subList, stat, i, j, tmpPath;

	try {
		stat = fs.statSync(dir);
		if (stat && stat.isDirectory()) {
			list = fs.readdirSync(dir);

			for (i = 0; i < list.length; ++i) {
				tmpPath = path.join(dir, list[i]);

				stat = fs.statSync(tmpPath);
				if (stat.isDirectory() && recursive) {
					subList = readDirRecursiveSync(tmpPath, recursive);
					for (j = 0; j < subList.length; ++j) {
						files.push(subList[j]);
					}
				} else if (stat.isFile()) {
					files.push(tmpPath);
				}
			}
		}
	} catch (e) {
		// Ignore... 'path' is probably a directory that doesn't exist.
	}

	return files;
};

var parseDirectory = function(filter, path) {
    var i, files = readDirRecursiveSync(path);

    for (i = 0; i < files.length; ++i) {
        parseFile(filter, files[i]);
    }
};

var parseFile = function(filter, filePath) {
	var itemPath = path.join(process.cwd(), filePath);

	switch (path.extname(itemPath)) {
		case '.h':
		case '.hpp':
			filter.source.include.push(itemPath);
			break;

		case '.c':
		case '.cpp':
			filter.source.compile.push(itemPath);
			break;

		case '.rc':
			filter.source.resource.push(itemPath);
			break;

		default:
			filter.source.ignore.push(itemPath);
			break;
	}
};

var parseSource = function(filter, source) {
	var i;

    if (source.getName()) {
        filter = createFilter(source.getName(), source.getExtensions(), filter);
    }

    if (source.getPaths()) {
        for (i = 0; i < source.getPaths().length; ++i) {
            parseDirectory(filter, source.getPath(i));
        }
    }

    if (source.getFiles()) {
        for (i = 0; i < source.getFiles().length; ++i) {
            parseFile(filter, source.getFile(i));
        }
    }

    var sources = source.getSources();
    for (i = 0; i < sources.length; ++i) {
        parseSource(filter, sources[i]);
    }

	return filter;
};

module.exports.parse = function(source) {
    var filter = initializeFilter();

    parseSource(filter, source);

    return filter;
};
