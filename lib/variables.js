var variableSets = [];
var projects = [];
var ideProjects = [];
var ideConfigurations = [];
var generatorName;

var parseGeneral = function(data) {
    data = data.replace(/\$\(generator.name\)/g, generatorName);

    return data;
};

var parseVariables = function(data) {
    var regex, varSet;
    if (variableSets.length) {
        varSet = variableSets[variableSets.length - 1]

        for (var name in varSet) {
            regex = new RegExp('\\$\\(' + name + '\\)', 'g');

            data = data.replace(regex, varSet[name]);
        }
    }
    return data;
};

var parseProject = function(data) {
    var proj;

    if (projects.length) {
        proj = projects[projects.length - 1];

        data = data.replace(/\$\(project.name\)/g, proj.getName());
    }

    return data;
};

var parseIdeProjects = function(data) {
    var ideProj;

    if (ideProjects.length) {
        ideProj = ideProjects[ideProjects.length - 1];

        data = data.replace(/\$\(ideproject.name\)/g, ideProj.getName());
    }

    return data;
};

var parseIdeConfiguration = function(data) {
    return data;
};

exports.parse = function(data) {
    if (data) {
        data = parseGeneral(data);
        data = parseVariables(data);
        data = parseProject(data);
        data = parseIdeProjects(data);
        data = parseIdeConfiguration(data);
    }
    return data;
};

exports.setGeneratorName = function(name) {
    generatorName = name;
};

exports.pushVariableSet = function(variables) {
    variableSets.push(variables);
};

exports.popVariableSet = function() {
    variableSets.pop();
};

exports.pushProject = function(project) {
    projects.push(project);
};

exports.popProject = function() {
    projects.pop();
};

exports.pushIdeProject = function(project) {
    ideProjects.push(project);
};

exports.popIdeProject = function() {
    ideProjects.pop();
};

exports.pushIdeConfiguration = function(configuration) {
    ideConfigurations.push(configuration);
};

exports.popIdeConfiguration = function() {
    ideConfigurations.pop();
};
