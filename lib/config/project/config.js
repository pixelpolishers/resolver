function Class() {
    var name;
    var vendorDirectory;
    var projectDirectory;
    var projects = [];
    var variables;
    var hideSolutionNode;
    var repositories = [];

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getVendorDirectory = function() {
        return vendorDirectory;
    };

    this.setVendorDirectory = function(value) {
        vendorDirectory = value;
    };

    this.getProjectDirectory = function() {
        return projectDirectory;
    };

    this.addProject = function(project) {
        projects.push(project);
    };

    this.getProject = function(i) {
        return projects[i];
    };

    this.hasProjects = function() {
        return projects.length !== 0;
    };

    this.getProjects = function() {
        return projects;
    };

    this.getVariables = function() {
        return variables;
    };

    this.getHideSolutionNode = function() {
        return hideSolutionNode;
    };

    this.setHideSolutionNode = function(value) {
        hideSolutionNode = value;
    };

    this.getDependencies = function() {
        var result = [];

        for (var i = 0; i < projects.length; ++i) {
            result = result.concat(projects[i].getDependencies());
        }

        return result;
    };

    this.addRepository = function(repository) {
        repositories.push(repository);
    };

    this.getRepositories = function() {
        return repositories;
    };
}

module.exports.Class = Class;
