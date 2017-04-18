(function () {
    angular.module('App.rsUsers', [
        'App.services.Runs',
        'App.services.User',
        'App.services.Users',
        'App.services.Modal',
        'App.services.Pagination',
        'App.services.Navigation',
        'App.cmpPaging'
    ]).directive('rsUsers', function ($timeout, User, $rootScope) {
        return {
            restrict: 'E',
            replace: true,
            scope: {},
            templateUrl: '/assets/users',
            controller: function (moment, $scope, Users, Modal, Pagination, Navigation, $http) {
                $scope.User = User;
                $scope.UserList = Users;
                $scope.Pagination = Pagination;


                $scope.data = [];

                $scope.subject = null;

                $scope.newSubject = function () {
                    $scope.subject = {
                        id: 0,
                        email: '',
                        password: '',
                        is_manager: 0,
                        is_admin: 0
                    };
                };


                $scope.getItems = function () {
                    return $scope.data.slice(Pagination.page * Pagination.onPage, Math.min(Pagination.onPage * (Pagination.page + 1), $scope.data.length));
                };

                $scope.loadData = function () {
                    Users.load(function () {
                        $scope.data = Users.records;
                        if (Navigation.currentState() == 'users')
                            Pagination.setTotal($scope.data.length);
                        Modal.hidePreloader();
                    });
                };

                $scope.subjectForm = $('.users-edit');
                $scope.showForm = function () {
                    Modal.showPreloader(function () {
                        $scope.subjectForm.show();
                    });
                };

                $scope.hideForm = function () {
                    $scope.subjectForm.hide();
                    Modal.hidePreloader();
                };

                $scope.subjectForm.find('button.save').on('click', function () {
                    if (!$scope.subject)
                        return $scope.hideForm();
                    $scope.hideForm();
                    Modal.showPreloader();
                    $http.post($scope.subject.id == 0 ? '/api/users/create' : '/api/users/update', $.extend($scope.subject, {
                        token: User.token
                    })).success(function (data, status) {
                        Modal.hidePreloader();
                        if (data.success) {
                            var text;
                            if ($scope.subject.id == 0) {
                                text = 'User added with password <b>' + data.password + '</b>';
                            }
                            else {
                                text = 'User updated';
                                if (data.password)
                                    text += ' and password set to <b>' + data.password + '</b>';
                            }
                            Modal.showModal(text, {
                                'OK': $scope.loadData
                            });
                        }
                        else {
                            Modal.showModal(data.errors, {
                                'OK': $scope.showForm
                            });
                        }
                    }).error(function (data, status) {
                        Modal.hidePreloader();
                        Modal.showModal("Error connecting to server");
                    })
                });

                $scope.subjectForm.find('button.cancel').on('click', function () {
                    $scope.hideForm();
                });

                $scope.deleteUser = function (id) {
                    Modal.showModal('Delete this record?', {
                        'Yes': function () {
                            $http.post('/api/users/delete', {
                                id: id,
                                token: User.token
                            }).success(function (data, status) {
                                Modal.hidePreloader();
                                if (data.success) {
                                    Modal.showModal('User deleted', {
                                        'OK': $scope.loadData
                                    });
                                }
                                else {
                                    Modal.showModal(data.errors);
                                }
                            }).error(function (data, status) {
                                Modal.hidePreloader();
                                Modal.showModal("Error connecting to server");
                            });
                        },
                        'No': null
                    })
                };

                $scope.editUser = function (id) {
                    var lookup = $.grep($scope.data, function (item) {
                        return item.id == id;
                    });
                    if (lookup.length)
                        $scope.subject = lookup[0];
                    else
                        return Modal.showModal('Invalid user id');
                    $scope.showForm();
                };

                $scope.createUser = function () {
                    $scope.newSubject();
                    $scope.showForm();
                };


                $rootScope.$on('navigation:go', function (event, args) {
                    if (args.state == 'users') {
                        if (User.isAuth() && (User.isManager || User.isAdmin)) {
                            Pagination.reset();
                            $scope.loadData();
                        }
                    }
                });

                $rootScope.$on('user:login', function (event, args) {
                    $scope.data = [];
                    Pagination.reset();
                    if (User.isManager || User.isAdmin)
                        $scope.loadData();

                });

                $scope.newSubject();
            },
            link: function ($scope, $elem) {

                if (User.isAuth())
                    $scope.loadData();
            }
        };
    });
}).call(this);