function Class() {
    var excludePaths = [];
    var executablePaths = [];
    var includePaths = [];
    var libraryPaths = [];
    var referencePaths = [];
    var sourcePaths = [];

    this.addExcludePath = function(path) {
        excludePaths.push(path);
    };

    this.getExcludePaths = function() {
        return excludePaths;
    };

    this.addExecutablePath = function(path) {
        executablePaths.push(path);
    };

    this.getExecutablePaths = function() {
        return executablePaths;
    };

    this.addIncludePath = function(path) {
        includePaths.push(path);
    };

    this.getIncludePaths = function() {
        return includePaths;
    };

    this.addLibraryPath = function(path) {
        libraryPaths.push(path);
    };

    this.getLibraryPaths = function() {
        return libraryPaths;
    };

    this.addReferencePath = function(path) {
        referencePaths.push(path);
    };

    this.getReferencePaths = function() {
        return referencePaths;
    };

    this.addSourcePath = function(path) {
        sourcePaths.push(path);
    };

    this.getSourcePaths = function() {
        return sourcePaths;
    };
}

module.exports.Class = Class;