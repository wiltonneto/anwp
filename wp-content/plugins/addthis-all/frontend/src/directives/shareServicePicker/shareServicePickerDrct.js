appAddThisWordPress.directive('shareServicePicker', function(
  wordpress,
  $timeout
) {
  return {
    scope: {
      pickedServices: '=services', // bi-directional
      numberOfServices: '=numberOfServices',
      min: '@min',
      max: '@max',
      toolPco: '@toolPco'
    },
    link: function($scope, el, attrs) {
      // set up functions

      $scope.isAutoPersonalized = function() {
        return $scope.auto_personalization;
      };

      $scope.emptyPickedServiceList = function() {
        $scope.pickedServices = [];
      };

      $scope.serviceAdded = function(service) {
        if ((typeof service === 'object') &&
          (typeof service.code !== 'undefined') &&
          (typeof $scope.pickedServices === 'object') &&
          $scope.pickedServices.indexOf(service.code) === -1) {
          return false;
        }

        return true;
      };

      $scope.serviceSearch = function(service) {
        var searchString = $scope.searchString.toLowerCase();
        if (service.searchString.toLowerCase().search(searchString) > -1) {
          return true;
        } else {
          return false;
        }
      };

      $scope.addService = function(service) {
        $scope.pickedServices.push(service.code);
        service.rank = addIncrement;
        addIncrement++;
      };

      $scope.deleteService = function(service) {
        var index = $scope.pickedServices.indexOf(service.code);
        if (index > -1) {
          $scope.pickedServices.splice(index, 1);
        }

        service.rank = -1;
      };

      var setServiceOptions = function(input) {
        var shareServices = angular.copy(input);
        shareServices.forEach(function(service, index) {
          service.rank = $scope.pickedServices.indexOf(service.code);
          service.index = index;
        });

        $scope.serviceOptions = shareServices;

        $timeout(function() {
          if (typeof window.addthis !== 'undefined') {
            window.addthis.toolbox('.share-service-picker');
          }
        });
      };

      // do actual stuff
      if (typeof $scope.pickedServices !== 'object') {
        $scope.emptyPickedServiceList();
      }
      var addIncrement = $scope.pickedServices.length;

      // in case the services load in late
      $scope.$watch('pickedServices', function(newValue) {
        if ((typeof newValue !== 'undefined') &&
          $scope.auto_personalization === true &&
          newValue.length > 0
        ) {
          $scope.auto_personalization = false;
        }
      });

      $scope.searchString = '';

      if ($scope.pickedServices.length === 0) {
        $scope.auto_personalization = true;
      } else {
        $scope.auto_personalization = false;
      }

      $scope.serviceOptions = [];
      // if the thirdParty attr is included, load third party services
      var servicesPromise;
      $scope.thirdParty = false;
      if (typeof attrs.thirdParty !== 'undefined') {
        servicesPromise = wordpress.thirdPartyGetShareServices();
        $scope.thirdParty = true;
      } else {
        servicesPromise = wordpress.addThisGetShareServices();
      }

      servicesPromise.then(setServiceOptions);
    },
    templateUrl: '/directives/shareServicePicker/shareServicePicker.html'
  };
});