function Class() {
    var name;
    var extensions = [];
    var files = [];
    var paths = [];
    var sources = [];

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.addExtension = function(value) {
        extensions.push(value);
    };

    this.getExtensions = function() {
        return extensions;
    };

    this.addFile = function(value) {
        files.push(value);
    };

    this.getFile = function(i) {
        return files[i];
    };

    this.getFiles = function() {
        return files;
    };

    this.addPath = function(value) {
        paths.push(value);
    };

    this.getPath = function(i) {
        return paths[i];
    };

    this.getPaths = function() {
        return paths;
    };

    this.addSource = function(value) {
        sources.push(value);
    };

    this.getSources = function() {
        return sources;
    };

    this.hasSources = function() {
        return sources.length !== 0;
    };
}

module.exports.Class = Class;