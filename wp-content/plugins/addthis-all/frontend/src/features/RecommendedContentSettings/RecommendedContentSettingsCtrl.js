appAddThisWordPress.controller('RecommendedContentSettingsCtrl', function(
  $scope,
  wordpress,
  $state,
  $stateParams
) {
  $scope.changeState = function(newState) {
    if (newState === 'all') {
      $state.go('recommend');
    } else {
      $state.go('recommend.pco', {toolPco: newState});
    }
  };

  $scope.showToolCards = function() {
    if (angular.isDefined($stateParams.toolPco) &&
      $stateParams.toolPco !== ''
    ) {
      return false;
    } else {
      return true;
    }
  };

  $scope.goBack = function() {
    $scope.changeState('all');
  };

  $scope.templateBaseUrl = wordpress.templateBaseUrl();

  $scope.globalOptions = {};
  $scope.recommendedContent = {};

  wordpress.globalOptions.get().then(function(data) {
    $scope.globalOptions = data;

    wordpress.recommendedContent
    .get($scope.globalOptions.addthis_plugin_controls)
    .then(function(data) {
      $scope.recommendedContent = data;

      if ($scope.globalOptions.addthis_plugin_controls === 'AddThis') {
        setPromotedUrls();
      }
    });
  });

  var setPromotedUrls = function() {
    return wordpress.getPromotedUrl().then(function(data) {
      angular.forEach(data, function(urls, toolPco) {
        if (typeof $scope.recommendedContent[toolPco] === 'object') {
          $scope.recommendedContent[toolPco].promotedUrl = urls[0];
        } else {
          $scope.recommendedContent[toolPco] = { promotedUrl: urls[0] };
        }
      });

      return data;
    });
  };

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;

    return wordpress.recommendedContent.save(
      $scope.globalOptions.addthis_plugin_controls,
      toolPco
    )
    .then(function(data) {
      $scope.recommendedContent = data;
      $scope.saving = false;

      return setPromotedUrls().then(function() {
        return data;
      });
    });
  };

  $scope.toggleEvent = function(toolPco) {
    if (angular.isDefined($scope.recommendedContent[toolPco]) &&
      angular.isDefined($scope.recommendedContent[toolPco].enabled) &&
      $scope.recommendedContent[toolPco].enabled === true
    ) {
      $scope.save(toolPco);
    } else if ($state.current.name === 'recommend') {
      $scope.changeState(toolPco);
    }
  };
});