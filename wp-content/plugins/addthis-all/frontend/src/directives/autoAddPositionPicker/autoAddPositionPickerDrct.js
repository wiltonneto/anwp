appAddThisWordPress.directive('autoAddPositionPicker', function() {
  return {
    scope: {
      boostArray: '=ngModel', // bi-directional
      defaultClass: '@defaultClass',
      toolPco: '@toolPco'
    },
    link: function(scope) {
      scope.checkboxes = [
        {
          'msgid': 'tool_settings_share_locations_homepage_title',
          'above': {
            'cssClass': 'at-above-post-homepage',
            'msgid': 'tool_settings_share_locations_above_excerpt_label',
            'value': true
          },
          'below': {
            'cssClass': 'at-below-post-homepage',
            'msgid': 'tool_settings_share_locations_below_excerpt_label',
            'value': true
          }
        },
        {
          'msgid': 'tool_settings_share_locations_post_title',
          'above': {
            'cssClass': 'at-above-post',
            'msgid': 'tool_settings_share_locations_above_blog_post_label',
            'value': true
          },
          'below': {
            'cssClass': 'at-below-post',
            'msgid': 'tool_settings_share_locations_below_blog_post_label',
            'value': true
          }
        },
        {
          'msgid': 'tool_settings_share_locations_page_title',
          'above': {
            'cssClass': 'at-above-post-page',
            'msgid': 'tool_settings_share_locations_above_page_label',
            'value': true
          },
          'below': {
            'cssClass': 'at-below-post-page',
            'msgid': 'tool_settings_share_locations_below_page_label',
            'value': true
          }
        },
        {
          'msgid': 'tool_settings_share_locations_category_title',
          'above': {
            'cssClass': 'at-above-post-cat-page',
            'msgid': 'tool_settings_share_locations_above_excerpt_label',
            'value': true
          },
          'below': {
            'cssClass': 'at-below-post-cat-page',
            'msgid': 'tool_settings_share_locations_below_excerpt_label',
            'value': true
          }
        },
        {
          'msgid': 'tool_settings_share_locations_archive_title',
          'above': {
            'cssClass': 'at-above-post-arch-page',
            'msgid': 'tool_settings_share_locations_above_excerpt_label',
            'value': true
          },
          'below': {
            'cssClass': 'at-below-post-arch-page',
            'msgid': 'tool_settings_share_locations_below_excerpt_label',
            'value': true
          }
        }
      ];

      var updateBooleans = function(locationObject) {
        var classString = '.' + locationObject.cssClass;
        if (scope.boostArray.indexOf(classString) > -1) {
          if (locationObject.value !== true) {
            locationObject.value = true;
          }
        } else {
          if (locationObject.value !== false) {
            locationObject.value = false;
          }
        }
      };

      var updateCheckboxes = function() {
        scope.checkboxes.forEach(function(pageType) {
          updateBooleans(pageType.above);
          updateBooleans(pageType.below);
        });
      };

      var updateBoostArray = function(locationObject) {
        var classString = '.' + locationObject.cssClass;
        var index = scope.boostArray.indexOf(classString);
        if (locationObject.value === true) {
          if (index === -1) {
            scope.boostArray.push(classString);
          }
        } else {
          if (index > -1) {
            scope.boostArray.splice(index, 1);
          }
        }
      };

      scope.reviewCheckboxes = function() {
        if (typeof scope.boostArray !== 'object' ||
          !Array.isArray(scope.boostArray)
        ) {
          scope.boostArray = [];
        }

        // check for default class and add if needed
        var classString = '.' + scope.defaultClass;
        if (scope.boostArray.indexOf(classString) === -1) {
          scope.boostArray.push(classString);
        }

        // check for above and below location classes and add if needed
        scope.checkboxes.forEach(function(pageType) {
          updateBoostArray(pageType.above);
          updateBoostArray(pageType.below);
        });
      };

      scope.$watch('boostArray', function(newValue) {
        if ((typeof newValue !== 'undefined') && Array.isArray(newValue)) {
          updateCheckboxes();
        }
      });
    },
    templateUrl: '/directives/autoAddPositionPicker/autoAddPositionPicker.html'
  };
});