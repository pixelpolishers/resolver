function Class() {
    var name;
    var version;

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getVersion = function() {
        return version;
    };

    this.setVersion = function(value) {
        version = value;
    };
}

module.exports.Class = Class;