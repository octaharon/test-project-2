(function () {
    angular.module('App.cmpHeader', [
        'App.services.User',
        'App.services.Modal',
        'App.services.Navigation'
    ]).directive('cmpHeader', function () {
        return {
            restrict: 'E',
            replace: true,
            scope: {},
            templateUrl: '/assets/header',
            controller: function ($rootScope, $scope, User, Modal, Navigation) {
                $scope.model = {
                    email: '',
                    password: ''
                };

                $scope.User = User;
                $scope.Navigation = Navigation;

                $scope.setState = function (state) {
                    if (Navigation.setState(state))
                        $rootScope.$emit('navigation:go', {
                            state: state
                        });
                };

                $scope.tryLogin = function () {
                    if (!$scope.model.email || !$scope.model.password) {
                        return Modal.showModal('Please enter e-mail and password');
                    }
                    User.signin($scope.model.email, $scope.model.password);
                };

                $scope.tryRegister = function () {
                    if (!$scope.model.email || !$scope.model.password) {
                        return Modal.showModal('Please enter e-mail and password');
                    }
                    User.signup($scope.model.email, $scope.model.password);
                };

                $scope.tryReminder = function () {
                    if (!$scope.model.email) {
                        return Modal.showModal('Please enter e-mail');
                    }
                    User.reminder($scope.model.email);
                };

                $scope.tryLogout = function () {
                    if (!User.isAuth())
                        return false;
                    User.logout();
                };

                $('.auth').on("keypress", function (e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                        $scope.tryLogin();
                    }
                });

            }
        };
    });
}).call(this);