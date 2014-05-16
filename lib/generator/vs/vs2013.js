/**
 * The Visual Studio 2010 generator.
 *
 * @class VS2010Generator
 * @constructor
 * @param application The application that is used.
 */
function Class(application) {
    var config = application.getConfig();
    var logger = require('../../logger');
    var variables = require('../../variables');
    var projectDirectory;
    var ProjectGeneratorClass = require('./vs2013/project').Class;
    var FilterGeneratorClass = require('./vs2013/filter').Class;
    var SolutionGeneratorClass = require('./vs2013/solution').Class;

    function generateProject(self, project) {
        var projectSrc;

        logger.log('Parsing ' + project.getName());

        // Create the UUID:
        project.setUuid(require('node-uuid').v4().toUpperCase());

        // Parse the source:
        // TODO: Should this move to the config and be more generic?
        projectSrc = require('./source').parse(project.getSource());

        // Generate the project file:
        var projectGenerator = new ProjectGeneratorClass(self, project);
        projectGenerator.setSource(projectSrc);
        projectGenerator.generate();

        // Generate the filterfile:
        var filterGenerator = new FilterGeneratorClass(self, project);
        filterGenerator.setSource(projectSrc);
        filterGenerator.generate();
    }

    this.getApplication = function() {
        return application;
    };

    this.getConfig = function() {
        return config;
    };

    this.getName = function() {
        return 'vs2013';
    };

    this.getProjectDirectory = function() {
        return projectDirectory;
    };

    this.setProjectDirectory = function(value) {
        projectDirectory = value;
    };

    this.generate = function(callback) {
        var i, projects = config.getProjects();

        for (i = 0; i < projects.length; ++i) {
            variables.pushIdeProject(projects[i]);
            generateProject(this, projects[i]);
            variables.popIdeProject();
        }

        var solutionGenerator = new SolutionGeneratorClass(this);
        solutionGenerator.generate();

        callback();
    };
}

module.exports.Class = Class;
