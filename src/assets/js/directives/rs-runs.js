(function () {
    angular.module('App.rsRuns', [
        'App.services.Runs',
        'App.services.User',
        'App.services.Users',
        'App.services.Modal',
        'App.filters.speed',
        'App.filters.duration',
        'App.services.Pagination',
        'App.services.Navigation',
        'App.cmpPaging'
    ]).directive('rsRuns', function ($timeout, User, $rootScope) {
        return {
            restrict: 'E',
            replace: true,
            scope: {},
            templateUrl: '/assets/runs',
            controller: function (moment, $scope, Runs, Users, Modal, Pagination, Navigation, $http) {
                $scope.Runs = Runs;
                $scope.User = User;
                $scope.UserList = Users;
                $scope.Pagination = Pagination;

                $scope.filter = {
                    dateFrom: null,
                    dateTo: null,
                    user_id: null
                };

                $scope.stats = null;

                $scope.data = [];

                $scope.subject = null;

                $scope.isOwner = function (userid) {
                    return userid == $scope.user_id;
                };

                $scope.newSubject = function () {
                    $scope.subject = {
                        id: 0,
                        distance: 0,
                        duration: 0,
                        date: moment().format('YYYY-MM-DD')
                    };
                    if (User.isAdmin) {
                        $scope.subject.user_id = User.id;
                        if ($scope.filter.user_id)
                            $scope.subject.user_id = $scope.filter.user_id;
                    }
                };

                $scope.clearStats = function () {
                    $scope.stats = {
                        lastWeekTotal: {
                            distance: 0,
                            time: 0,
                            entries: 0
                        },
                        lastWeekAvg: {
                            distance: 0,
                            time: 0,
                            spd: 0
                        },
                        totalAvg: {
                            distance: 0,
                            time: 0,
                            entries: 0,
                            spd: 0
                        }
                    };
                };

                $scope.resetFilter = function () {
                    $scope.filter = {
                        dateFrom: null,
                        dateTo: null,
                        user_id: (User.isAdmin ? User.id : null)
                    }
                };

                $scope.getItems = function () {
                    return $scope.data.slice(Pagination.page * Pagination.onPage, Math.min(Pagination.onPage * (Pagination.page + 1), $scope.data.length));
                };


                $scope.loadData = function () {
                    Runs.user_id = $scope.filter.user_id || null;
                    Runs.load($scope.filter.dateFrom, $scope.filter.dateTo, function () {
                        $scope.data = Runs.records;
                        if (Navigation.currentState() == 'runs')
                            Pagination.setTotal($scope.data.length);
                        $scope.clearStats();
                        var endDataPoint = $scope.filter.dateTo ? moment($scope.filter.dateTo) : moment();
                        $.each($scope.data, function (key, record) {
                            var day = moment(record.date);
                            $scope.stats.totalAvg.distance += record.distance;
                            $scope.stats.totalAvg.time += record.duration;
                            $scope.stats.totalAvg.entries++;
                            if (endDataPoint.diff(day, 'days') <= 7) {
                                $scope.stats.lastWeekTotal.distance += record.distance;
                                $scope.stats.lastWeekTotal.time += record.duration;
                                $scope.stats.lastWeekTotal.entries++;
                            }
                        });
                        if ($scope.stats.lastWeekTotal.entries) {
                            $scope.stats.lastWeekAvg.distance = $scope.stats.lastWeekTotal.distance / $scope.stats.lastWeekTotal.entries;
                            $scope.stats.lastWeekAvg.spd = $scope.stats.lastWeekTotal.distance / $scope.stats.lastWeekTotal.time;
                        }
                        if ($scope.stats.totalAvg.entries) {
                            $scope.stats.totalAvg.spd = $scope.stats.totalAvg.distance / $scope.stats.totalAvg.time;
                            $scope.stats.totalAvg.distance = $scope.stats.totalAvg.distance / $scope.stats.totalAvg.entries;

                        }
                        Modal.hidePreloader();
                        if (User.isAdmin && !$scope.UserList.records) {
                            if (!$scope.filter.user_id)
                                $scope.filter.user_id = User.id;
                            Users.load(function () {
                                Modal.hidePreloader();
                            });
                        }
                    });
                };

                $scope.subjectForm = $('.runs-edit');
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
                    $http.post($scope.subject.id == 0 ? '/api/runs/create' : '/api/runs/update', $.extend($scope.subject, {
                        token: User.token
                    })).success(function (data, status) {
                        Modal.hidePreloader();
                        if (data.success) {
                            var text;
                            if ($scope.subject.id == 0) {
                                text = 'Record added';
                                if ($scope.subject.user_id && $scope.subject.user_id != User.id) {
                                    text += ' for user ' + data.user;
                                }
                            }
                            else
                                text = 'Record updated';
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

                $scope.deleteRun = function (id) {
                    Modal.showModal('Delete this record?', {
                        'Yes': function () {
                            $http.post('/api/runs/delete', {
                                id: id,
                                token: User.token
                            }).success(function (data, status) {
                                Modal.hidePreloader();
                                if (data.success) {
                                    Modal.showModal('Record deleted', {
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

                $scope.editRun = function (id) {
                    var lookup = $.grep($scope.data, function (item) {
                        return item.id == id;
                    });
                    if (lookup.length)
                        $scope.subject = lookup[0];
                    else
                        return Modal.showModal('Invalid record id');
                    $scope.showForm();
                };

                $scope.createRun = function () {
                    $scope.newSubject();
                    $scope.showForm();
                };


                $rootScope.$on('navigation:go', function (event, args) {
                    if (args.state == 'runs') {
                        if (User.isAuth()) {
                            Pagination.reset();
                            $scope.resetFilter();
                            $scope.loadData();
                        }
                    }
                });

                $rootScope.$on('user:login', function (event, args) {
                    $scope.resetFilter();
                    $scope.clearStats();
                    $scope.newSubject();
                    Pagination.reset();
                    $scope.data = [];
                    if (User.isAdmin) {
                        $scope.filter.user_id = args.id;
                    }
                    $scope.loadData();

                });

                $scope.clearStats();
                $scope.newSubject();
            },
            link: function ($scope, $elem) {

                if (User.isAuth())
                    $scope.loadData();
            }
        };
    });
}).call(this);