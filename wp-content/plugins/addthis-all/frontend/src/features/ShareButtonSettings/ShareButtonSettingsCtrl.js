appAddThisWordPress.controller('ShareButtonSettingsCtrl', function(
  $scope,
  wordpress,
  $stateParams,
  $state
) {
  $scope.changeState = function(newState) {
    if (newState === 'all') {
      $state.go('share');
    } else {
      $state.go('share.pco', {toolPco: newState});
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
  $scope.shareButtons = {};

  wordpress.globalOptions.get().then(function(globalOptions) {
    $scope.globalOptions = globalOptions;

    wordpress.sharingButtons.get($scope.globalOptions.addthis_plugin_controls)
    .then(function(shareButtons) {
      $scope.shareButtons = shareButtons;
    });
  });

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;

    return wordpress.sharingButtons
    .save($scope.globalOptions.addthis_plugin_controls, toolPco)
    .then(function(data) {
      $scope.shareButtons = data;
      $scope.saving = false;
      return data;
    });
  };

  $scope.toggleEvent = function(toolPco) {
    if (angular.isDefined($scope.shareButtons[toolPco]) &&
      angular.isDefined($scope.shareButtons[toolPco].enabled) &&
      $scope.shareButtons[toolPco].enabled === true
    ) {
      $scope.save(toolPco);
    } else if ($state.current.name === 'share') {
      $scope.changeState(toolPco);
    }
  };
});