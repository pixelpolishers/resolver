function Class(generator, project) {
    var root, source, fs = require('fs'), variables = require('../../../variables');

    function writeFilterName(filter) {
        var filterName = filter.name;

        while (filter && filter.parent && filter.parent.name) {
            filterName = filter.parent.name + '\\' + filterName;
            filter = filter.parent;
        }

        return filterName;
    }

    function writeItemGroupContent(parentElement, filter, elementName, key) {
        if (filter.source && filter.source[key]) {
            for (var i = 0; i < filter.source[key].length; ++i) {
                var element = parentElement.ele(elementName);
                element.att('Include', filter.source[key][i]);
                if (filter.name) {
                    element.ele('Filter', writeFilterName(filter));
                }
            }
        }

        for (var i = 0; i < filter.filters.length; ++i) {
            writeItemGroupContent(parentElement, filter.filters[i], elementName, key);
        }
    }

    function writeItemGroupFilters() {
        var element = root.ele('ItemGroup');

        writeItemGroupFilter(element, source);
    };

    function writeItemGroupNone() {
        var element = root.ele('ItemGroup');

        writeItemGroupContent(element, source, 'None', 'ignore');
    };

    function writeItemGroupIncludes() {
        var element = root.ele('ItemGroup');

        writeItemGroupContent(element, source, 'ClInclude', 'include');
    };

    function writeItemGroupSource() {
        var element = root.ele('ItemGroup');

        writeItemGroupContent(element, source, 'ClCompile', 'compile');
    };

    function writeItemGroupResources() {
        var element = root.ele('ItemGroup');

        writeItemGroupContent(element, source, 'ResourceCompile', 'resource');
    };

    function writeItemGroupFilter(element, filter) {
        if (filter.name) {
            var filterElement = element.ele('Filter');
            filterElement.att('Include', writeFilterName(filter));
            filterElement.ele('UniqueIdentifier', require('node-uuid').v4().toUpperCase());
            filterElement.ele('SourceControlFiles', 'True');
            filterElement.ele('ParseFiles', 'True');
            if (filter.extensions && filter.extensions.length) {
                filterElement.ele('Extensions', filter.extensions.join(';'));
            }
        }

        for (var i = 0; i < filter.filters.length; ++i) {
            writeItemGroupFilter(element, filter.filters[i]);
        }
    }

    function writeFilterFile() {
        root = require('xmlbuilder').create('Project', {
            'version': '1.0',
            'encoding': 'UTF-8'
        });
        root.att('ToolsVersion', '4.0');
        root.att('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');

        writeItemGroupFilters();
        writeItemGroupNone();
        writeItemGroupIncludes();
        writeItemGroupSource();
        writeItemGroupResources();

        return root.end({pretty: true}).toString('utf8');
    };

    this.setSource = function(value) {
        source = value;
    };

    this.generate = function() {
        var path = generator.getProjectDirectory()
                + '/' + variables.parse(project.getName()) + '.vcxproj.filters';

        fs.writeFileSync(path, writeFilterFile());
    };
}

module.exports.Class = Class;
