(function () {
    angular.module('App.services.Users', [
        'App.services.Modal',
        'App.services.User'
    ]).factory('Users', function ($http, $filter, Modal, User) {
        var Users = {
            records: null,

            load: function (callback) {
                if (!User.isAuth() || !(User.isManager || User.isAdmin))
                    return false;
                var data = {
                    token: User.token
                };
                Modal.showPreloader();
                $http.post('/api/users/read', data).success(function (data, status) {
                    Modal.hidePreloader();
                    if (data.success) {
                        Users.records = data.data;
                        if (callback instanceof Function) {
                            callback();
                        }
                    }
                    else {
                        Users.records = null;
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    Modal.showModal("Can't load user list");
                });
            }
        };

        return Users;
    });
}).call(this);