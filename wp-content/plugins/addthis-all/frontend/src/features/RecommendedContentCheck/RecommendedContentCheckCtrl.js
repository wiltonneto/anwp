appAddThisWordPress.controller('RecommendedContentCheckCtrl', function(
  $scope,
  wordpress
) {
  $scope.globalOptions = {};
  wordpress.globalOptions.get().then(function(data) {
    $scope.globalOptions = data;
  });

  $scope.haveRecommendedContent = true;
  wordpress.addThisRecommendedContent().then(function(data) {
    if (data.success === false) {
      $scope.haveRecommendedContent = false;
    }
  });
});