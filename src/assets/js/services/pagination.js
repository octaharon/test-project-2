(function () {
    angular.module('App.services.Pagination', []).factory('Pagination', function () {
        var Pagination = {
            page: 0,
            total: 0,
            pageCount: 1,
            onPage: 5,
            reset: function () {
                Pagination.page = 0;
                Pagination.total = 0;
                Pagination.pageCount = 1;
            },
            setTotal: function (total) {
                Pagination.total = parseInt(total);
                Pagination.pageCount = Math.ceil(Pagination.total / Pagination.onPage);
                if (Pagination.page * Pagination.onPage >= Pagination.total)
                    Pagination.page = Math.floor(Pagination.total / Pagination.onPage);
            }
        };

        return Pagination;

    });
}).call(this);
