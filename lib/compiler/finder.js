function Class(application) {
    this.find = function() {
        var i, compilers = [], isWin = !!process.platform.match(/^win/);

        if (isWin) {
            //compilers.push(require('./msvc/msvc2005'));
            //compilers.push(require('./msvc/msvc2008'));
            compilers.push(require('./msvc/msvc2010'));
            //compilers.push(require('./msvc/msvc2012'));
            //compilers.push(require('./msvc/msvc2013'));
        }

        for (i = 0; i < compilers.length; ++i) {
            if (compilers[i].checkAvailability()) {
                return compilers[i].Class;
            }
        }
    };
}

module.exports.Class = Class;
