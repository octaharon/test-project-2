(function() {

    angular.module('App.filters.capitalize', []).filter('capitalize', function() {
        return function(input) {
            if (input && typeof(input) == 'string') {
                return input.charAt(0).toUpperCase() + input.substr(1);
            } else {
                return input;
            }
        };
    });

}).call(this);
