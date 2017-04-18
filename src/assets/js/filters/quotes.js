(function () {

    angular.module('App.filters.quotes', []).filter('quotes', function (locale) {
        return function (input) {
            if (input && typeof(input) == 'string') {
                input = input.replace(/\"([^\"]+)\"/g, function (full, match) {
                    return "«" + match + "»"
                });
                return input.replace(/\s+-\s+/g, ' — ');
            } else {
                return input;
            }
        };
    });

}).call(this);
