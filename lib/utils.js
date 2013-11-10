/**
 * Counds the amount of members that the given object has.
 *
 * @param obj The object to count the members of.
 * @return Integer
 */
exports.countObjectMembers = function(obj) {
    var result = 0, i;

    for (i in obj) {
        result++;
    }

    return result;
};