(function () {
    angular.module('App.services.User', ['App.services.Modal']).factory('User', function ($http, $filter, Modal, $rootScope) {
        var User = {
            isManager: false,
            isAdmin: false,
            token: null,
            email: null,
            id: null,
            clearInfo: function () {
                User.isManager = false;
                User.isAdmin = false;
                User.token = null;
                User.id = null;
                User.email = null;
                $.removeCookie('user-token', {path: '/'});
            },
            isAuth: function () {
                return this.token && this.email;
            },
            init: function () {
                if ($.cookie('user-token')) {
                    User.token = $.cookie('user-token');
                    User.signinWithToken();
                }
                else
                    User.clearInfo();
            },
            signinWithToken: function () {
                if (!User.token)
                    return false;
                Modal.showPreloader();
                $http.post('/api/signin', {
                    token: User.token
                }).success(function (data) {
                    Modal.hidePreloader();
                    if (data.success) {
                        User.setUserData(data.user);
                    }
                    else {
                        User.clearInfo();
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    User.clearInfo();
                });
            },
            signin: function (email, password) {
                Modal.showPreloader();
                $http.post('/api/signin', {
                    email: email,
                    password: password
                }).success(function (data) {
                    Modal.hidePreloader();
                    if (data.success) {
                        User.setUserData(data.user);
                    }
                    else {
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    User.clearInfo();
                });
            },
            setUserData: function (data) {
                User.email = data.email;
                User.isAdmin = data.is_admin;
                User.isManager = data.is_manager;
                User.id = data.id;
                if (data.token) {
                    $.cookie('user-token', data.token, {path: '/', expires: 1});
                    User.token = data.token;
                }
                else
                    User.token = null;
                $rootScope.$emit('user:login', {
                    id: data.id
                });
            },
            signup: function (email, password) {
                Modal.showPreloader();
                $http.post('/api/signup', {
                    email: email,
                    password: password
                }).success(function (data) {
                    Modal.hidePreloader();
                    if (data.success) {
                        Modal.showModal('Successfully created an account');
                        User.signin(email, password);
                    }
                    else {
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    User.clearInfo();
                });
            },
            reminder: function (email) {
                Modal.showPreloader();
                $http.post('/api/reminder', {
                    email: email,
                }).success(function (data) {
                    Modal.hidePreloader();
                    if (data.success) {
                        Modal.showModal('Check your e-mail for a new password');
                    }
                    else {
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    User.clearInfo();
                });
            },
            logout: function () {
                if (!User.token)
                    return false;
                Modal.showPreloader();
                $http.post('/api/logout', {
                    token: User.token,
                }).success(function (data) {
                    Modal.hidePreloader();
                    if (data.success) {
                        User.clearInfo();
                    }
                    else {
                        Modal.showModal(data.errors);
                    }
                }).error(function (data, status) {
                    Modal.hidePreloader();
                    User.clearInfo();
                });
            }

        };

        return User;
    });
}).call(this);