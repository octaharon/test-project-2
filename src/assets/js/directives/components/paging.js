(function () {
    angular.module('App.cmpPaging', [
        'App.services.Pagination'
    ]).directive('cmpPaging', function () {
        return {
            restrict: 'E',
            replace: true,
            scope: {},
            templateUrl: '/assets/paging',
            controller: function ($rootScope, $scope, Pagination) {
                $scope.Pagination = Pagination;

                $scope.getPages = function () {
                    return new Array(Pagination.pageCount);
                };

                $scope.prevPage = function () {
                    if (Pagination.page > 0)
                        Pagination.page--;
                };

                $scope.nextPage = function () {
                    if (Pagination.page < Pagination.pageCount - 1)
                        Pagination.page++;
                };

                $scope.toPage = function (num) {
                    num = parseInt(num);
                    if (num >= 0 && num <= Pagination.pageCount - 1)
                        Pagination.page = num;
                }
            }
        };
    });
}).call(this);