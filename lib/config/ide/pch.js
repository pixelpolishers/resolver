function Class() {
    var name;
    var header;
    var source;
    var memory;

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getHeader = function() {
        return header;
    };

    this.setHeader = function(value) {
        header = value;
    };

    this.getSource = function() {
        return source;
    };

    this.setSource = function(value) {
        source = value;
    };

    this.getMemory = function() {
        return memory;
    };

    this.setMemory = function(value) {
        memory = value;
    };
}

module.exports.Class = Class;