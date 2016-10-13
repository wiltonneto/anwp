appAddThisWordPress.controller('FollowButtonSettingsCtrl', function(
  $scope,
  wordpress,
  $stateParams,
  $state
) {
  $scope.changeState = function(newState) {
    if (newState === 'all') {
      $state.go('follow');
    } else {
      $state.go('follow.pco', {toolPco: newState});
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
  $scope.followButtons = {};

  wordpress.globalOptions.get().then(function(globalOptions) {
    $scope.globalOptions = globalOptions;

    wordpress.followButtons.get($scope.globalOptions.addthis_plugin_controls)
    .then(function(followButtons) {
      $scope.followButtons = followButtons;
      if (angular.isDefined(followButtons.flwh) &&
        angular.isDefined(followButtons.flwh.conflict) &&
        followButtons.flwh.conflict === true
      ) {
        $state.go('follow_conflict', {toolPco: 'flwh'});
      } else if (angular.isDefined(followButtons.flwv) &&
        angular.isDefined(followButtons.flwv.conflict) &&
        followButtons.flwv.conflict === true
      ) {
        $state.go('follow_conflict', {toolPco: 'flwv'});
      }
    });
  });

  $scope.saving = false;
  $scope.save = function(toolPco) {
    $scope.saving = true;

    return wordpress.followButtons.save(
      $scope.globalOptions.addthis_plugin_controls,
      toolPco
    )
    .then(function(data) {
      $scope.followButtons = data;
      $scope.saving = false;
      return data;
    });
  };

  $scope.toggleEvent = function(toolPco) {
    if (angular.isDefined($scope.followButtons[toolPco]) &&
      angular.isDefined($scope.followButtons[toolPco].enabled) &&
      $scope.followButtons[toolPco].enabled === true
    ) {
      $scope.save(toolPco);
    } else if ($state.current.name === 'follow') {
      $scope.changeState(toolPco);
    }
  };
});