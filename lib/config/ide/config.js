function Class(project) {
    var name;
    var platform;
    var intermediateDirectory;
    var outputPath;
    var paths;
    var debug;
    var characterSet;
    var precompiledHeader;
    var warningLevel;
    var warningsAsErrors;
    var msvcErrorReport;
    var definitions = [];
    var dependencies = [];
    var inlineExpansion;

    this.getProject = function() {
        return project;
    };

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getPlatform = function() {
        return platform;
    };

    this.setPlatform = function(value) {
        platform = value;
    };

    this.getOutputPath = function() {
        return outputPath;
    };

    this.setOutputPath = function(value) {
        outputPath = value;
    };

    this.getIntermediateDirectory = function() {
        return intermediateDirectory;
    };

    this.setIntermediateDirectory = function(value) {
        intermediateDirectory = value;
    };

    this.getPaths = function() {
        return paths;
    };

    this.setPaths = function(value) {
        paths = value;
    };

    this.getDebug = function() {
        return debug;
    };

    this.setDebug = function(value) {
        debug = value;
    };

    this.getCharacterSet = function() {
        return characterSet;
    };

    this.setCharacterSet = function(value) {
        characterSet = value;
    };

    this.getPrecompiledHeader = function() {
        return precompiledHeader;
    };

    this.setPrecompiledHeader = function(value) {
        precompiledHeader = value;
    };

    this.getWarningLevel = function() {
        return warningLevel;
    };

    this.setWarningLevel = function(value) {
        warningLevel = value;
    };

    this.getWarningsAsErrors = function() {
        return warningsAsErrors;
    };

    this.setWarningsAsErrors = function(value) {
        warningsAsErrors = value;
    };

    this.getDefinitions = function() {
        return definitions;
    };

    this.setDefinitions = function(value) {
        definitions = value;
    };

    this.addDependency = function(dependency) {
        dependencies.push(dependency);
    };

    this.getDependencies = function() {
        return dependencies;
    };

    this.setDependencies = function(value) {
        dependencies = value;
    };

    this.getInlineExpansion = function() {
        return inlineExpansion;
    };

    this.setInlineExpansion = function(value) {
        inlineExpansion = value;
    };

    this.getMsvcErrorReport = function() {
        return msvcErrorReport;
    };

    this.setMsvcErrorReport = function(value) {
        msvcErrorReport = value;
    };
}

module.exports.Class = Class;