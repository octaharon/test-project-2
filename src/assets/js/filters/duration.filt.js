(function () {

    angular.module('App.filters.duration', []).filter('duration', function (moment) {
        return function (seconds) {
            seconds = Math.round(seconds);
            var minutes = Math.floor(seconds / 60);
            var sec = seconds % 60;
            if (sec < 10) sec = '0' + sec;
            return minutes + 'm ' + sec + 's';
        };
    });

}).call(this);
