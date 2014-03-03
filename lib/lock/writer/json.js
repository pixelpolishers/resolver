function Class() {
    this.write = function(file, path) {
        var data = {
            'dependencies': file.getDependencies()
        };
        
        require('fs').writeFileSync(path, JSON.stringify(data), 'utf-8');

        return file;
    };
}

module.exports.Class = Class;