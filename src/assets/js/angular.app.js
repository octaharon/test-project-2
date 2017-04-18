"use strict";

angular.module('app', [
    'ngRoute',
    'ngTouch',
    'ngAnimate',
    'angularMoment',
    'App.filters.slice',
    'App.filters.duration',
    'App.filters.numberFixedLen',
    'App.filters.capitalize',
    'App.filters.quotes',
    'App.filters.speed',
    'App.services.Modal',
    'App.services.User',
    'App.services.Navigation',
    'App.services.Runs',
    'App.services.Users',
    'App.services.Pagination',
    'App.cmpPaging',
    'App.rsRuns',
    'App.rsUsers',
    'App.cmpHeader'
])
    .config(function ($sceProvider, $httpProvider) {
        var transformRequestParams = function (obj) {
            var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

            for (name in obj) {
                value = obj[name];

                if (value === false)
                    continue;
                if (value instanceof Array) {
                    for (i = 0; i < value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += transformRequestParams(innerObj) + '&';
                    }
                }
                else if (value instanceof Object) {
                    for (subName in value) {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += transformRequestParams(innerObj) + '&';
                    }
                }
                else if (value !== undefined && value !== null)
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
        $httpProvider.defaults.transformRequest = [function (data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? transformRequestParams(data) : data;
        }];
        //$sceProvider.enabled(false);
    }).run(function (amMoment, $filter, Modal, User) {
    //debugger;
    Modal.init();
    User.init();


}).controller('defaultController', ['$scope', '$rootScope', 'Navigation', 'User', 'Runs', function ($scope, $rootScope, Navigation, User, Runs) {
    $scope.Navigation = Navigation;
    $scope.User = User;
}]).directive("ngDatepicker", function () {
    return {
        restrict: "A",
        link: function ($scope, $elem) {
            $elem.datetimepicker({
                format: 'Y-m-d',
                timepicker: false,
                onChangeDateTime: function (dp, $input) {
                    console.log(dp);
                }
            });
        }
    };
}).directive("ngInteger", function () {
    return {
        restrict: "A",
        link: function ($scope, $elem) {
            $elem.on('change blur keyup', function () {
                var value = $elem.val();
                value = parseInt(value);
                if (isNaN(value))
                    value = '';
                $elem.val(value);
            });
        }
    };
});