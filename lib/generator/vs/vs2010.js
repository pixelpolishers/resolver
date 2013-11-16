/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../../logger');

/**
 * The variable parser.
 *
 * @var Object
 */
var variables = require('../../variables');

/**
 * Generates the project files for this generator.
 *
 * @param settings The configuration settings of the project.
 * @param callback The callback method that is called once generating is done.
 * @return void
 */
exports.generate = function(settings, callback) {
    var i, projectDir, projectSrc, project, parsedProjectCount = 0;

    variables.setGeneratorName('vs2010');
    
    if (!settings.projects) {
        logger.error('No projects found to generate.');
        callback();
        return;
    }
    
    projectDir = settings.buildDir || 'build';
    projectDir = variables.parse(projectDir);
    
    require('mkdirp').mkdirp.sync(projectDir, 0777);
    
	for (i = 0; i < settings.projects.length; ++i) {
        project = settings.projects[i];
		project.uuid = require('node-uuid').v4().toUpperCase();
        
        if (!project.name) {
            logger.error('Failed to parse a project. No name set.');
            continue;
        }
        
        logger.log('Parsing ' + project.name);
        
		variables.pushIdeProject(project);

        if (project.source) {
            projectSrc = require('./source').parse(project.source);
            
            require('./vs2010/project').create(settings, project, projectSrc, projectDir);
            require('./vs2010/filter').create(settings, project, projectSrc, projectDir);
            parsedProjectCount++;
        } else {
            logger.error('Project ' + project.name + " has no sources to parse...");
        }
        
		variables.popIdeProject();
	}
    
    if (parsedProjectCount) {
        require('./vs2010/solution').create(settings, projectDir);
    }
    
	callback();
};
