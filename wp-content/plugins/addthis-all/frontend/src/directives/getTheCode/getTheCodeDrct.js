appAddThisWordPress.directive('getTheCode', function(wordpress) {
  return {
    scope: {
      shortcode: '@shortcode',
      name: '@widgetName'
    },
    transclude: true,
    controller: function($scope) {
      $scope.widgetConfigUrl = wordpress.widgetConfigUrl();
    },
    templateUrl: '/directives/getTheCode/getTheCode.html'
  };
});