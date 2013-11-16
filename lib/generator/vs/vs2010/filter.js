/**
 * The file system module used to the config files.
 *
 * @var Object
 */
var fs = require('fs');

/**
 * The UUID generator.
 *
 * @var Object
 */
var uuid = require('node-uuid');

/**
 * The variable parser.
 *
 * @var Object
 */
var variables = require('../../../variables');

var writeFilterName = function(filter) {
	var filterName = filter.name;

	while (filter && filter.parent && filter.parent.name) {
		filterName = filter.parent.name + '\\' + filterName;
		filter = filter.parent;
	}

	return filterName;
};

var writeItemGroupIncludes = function(parentElement, projectSrc) {
    var element, clElement, i, buildFilter = function(filter) {
        if (filter.source && filter.source.include) {
            for (i = 0; i < filter.source.include.length; ++i) {
                clElement = element.ele('ClInclude');
                clElement.att('Include', filter.source.include[i]);
                if (filter.name) {
                    clElement.ele('Filter', writeFilterName(filter));
                }
            }
        }

        for (i = 0; i < filter.filters.length; ++i) {
            buildFilter(filter.filters[i]);
        }
    };
    
    element = parentElement.ele('ItemGroup');

    buildFilter(projectSrc);
};

var writeItemGroupSource = function(parentElement, projectSrc) {
    var element, clElement, i, buildFilter = function(filter) {
        if (filter.source && filter.source.compile) {
            for (i = 0; i < filter.source.compile.length; ++i) {
                clElement = element.ele('ClCompile');
                clElement.att('Include', filter.source.compile[i]);
                if (filter.name) {
                    clElement.ele('Filter', writeFilterName(filter));
                }
            }
        }

        if (filter.filters) {
            for (i = 0; i < filter.filters.length; ++i) {
                buildFilter(filter.filters[i]);
            }
        }
    };
    
    element = parentElement.ele('ItemGroup');

    buildFilter(projectSrc);
};

var writeItemGroupFilters = function(parentElement, projectSrc) {
    var i, element, filterElement, buildFilter = function(source) {
        if (source.name) {
            filterElement = element.ele('Filter');
            filterElement.att('Include', writeFilterName(source));
            filterElement.ele('UniqueIdentifier', uuid.v4().toUpperCase());
			if (source.extensions && source.extensions.length) {
				filterElement.ele('Extensions', filter.extensions.join(';'));
			}
        }
        
        for (i = 0; i < source.filters.length; ++i) {
            buildFilter(source.filters[i]);
        }
    };
    
    element = parentElement.ele('ItemGroup');
    
    buildFilter(projectSrc);
};

var writeFilterFile = function(project, projectSrc) {
    var root;

    root = require('xmlbuilder').create('Project', {
        'version': '1.0', 
        'encoding': 'UTF-8'
    });
    root.att('ToolsVersion', '4.0');
    root.att('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');
    
    writeItemGroupFilters(root, projectSrc);
    writeItemGroupIncludes(root, projectSrc);
    writeItemGroupSource(root, projectSrc);

    return root.end({pretty: true}).toString('utf8');
};

/**
 * Creates the project filter file.
 *
 * @param project The project to create the file for.
 * @param settings The settings used to create the project file.
 * @param projectDir The directory to write to.
 */
exports.create = function(settings, project, projectSrc, projectDir) {
    var outputPath = projectDir + '/' + variables.parse(project.name) + '.vcxproj.filters';
    
    fs.writeFileSync(outputPath, writeFilterFile(project, projectSrc));
};
