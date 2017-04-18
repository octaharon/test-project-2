(function () {
    angular.module('App.services.Runs', [
        'App.services.Modal',
        'App.services.User'
    ]).factory('Runs', function ($http, $filter, Modal, User) {
        var Runs = {
            user_id: null,
            records: null,

            load: function (date_from, date_to, callback) {
                if (!User.isAuth())
                    return false;
                var data = {
                    token: User.token
                };
                if (Runs.user_id)
                    data.id = Runs.user_id;
                if (date_from)
                    data.date_from = date_from;
                if (date_to)
                    data.date_to = date_to;
                Modal.showPreloader();
                $http.post('/api/runs/read', data).success(function (data, status) {
                    Modal.hidePreloader();
                    if (data.success) {
                        Runs.records = data.data;
                        if (callback instanceof Function) {
                            callback();
                        }
                    }
                    else {
                        Runs.records = null;
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    Modal.showModal("Can't load records for user");
                });
            }
        };

        return Runs;
    });
}).call(this);