// Generated by CoffeeScript 1.4.0
(function() {

  angular.module('App.filters.slice', []).filter('slice', function() {
    return function(arr, count, begin) {
      var i, result, _i, _ref;
      result = [];
      for (i = _i = begin, _ref = begin + count; begin <= _ref ? _i <= _ref : _i >= _ref; i = begin <= _ref ? ++_i : --_i) {
        if (arr[i] !== void 0) {
          result.push(arr[i]);
        }
      }
      return result;
    };
  });

}).call(this);
