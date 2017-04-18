(function () {

    angular.module('App.filters.urlencode', []).filter('urlencode', function () {
        return function (input) {
            if (input) {
                return window.encodeURIComponent(input);
            } else {
                return input;
            }
        };
    });

}).call(this);
