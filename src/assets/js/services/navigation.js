(function () {
    angular.module('App.services.Navigation', [
        'App.services.User'
    ]).factory('Navigation', function ($http, $filter, User) {
        var Navigation = {
            state: 'runs',
            available_states: ['runs', 'users'],
            currentState: function () {
                return Navigation.state;
            },
            setState: function (state) {
                if ($.inArray(state, Navigation.available_states) == -1)
                    return false;
                if (state == 'users') {
                    if (User.isAdmin || User.isManager)
                        return Navigation.state = 'users';
                    else return false;
                }
                return Navigation.state = state;
            }
        };

        return Navigation;
    });
}).call(this);