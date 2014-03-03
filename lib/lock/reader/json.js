function Class() {
    this.read = function(path) {
        var FileClass = require('../file').Class;
        var file = new FileClass();

        if (require('fs').existsSync(path)) {
            var fileData = require('fs').readFileSync(path, 'utf-8');
            var fileJson = JSON.parse(fileData);

            file.setDependencies(fileJson.dependencies);
        }

        return file;
    };
}

module.exports.Class = Class;