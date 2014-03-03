function Class() {
    var type;
    var params = [];

    this.getType = function() {
        return type;
    };

    this.setType = function(value) {
        type = value;
    };
    
    this.getParam = function(name) {
        return params[name];
    };

    this.setParam = function(name, value) {
        params[name] = value;
    };
}

module.exports.Class = Class;
