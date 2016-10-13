appAddThisWordPress.directive('onOffSelect', function() {
  return {
    scope: {
      ngModel: '=ngModel', // bi-directional
      label: '@label',
      name: '@for'
    },
    transclude: true,
    link: function(scope, el, attrs, ctrl, transclude) {
      var transcludeElements = el.find('.transclude-here-on-off-select');
      if (transcludeElements.length === 0) {
        // for older version of jQuery :-(
        transcludeElements = jQuery(el)
        .find('[class*="transclude-here-basic-select"]');
      }
      transcludeElements.append(transclude());

      scope.options = [
        {
          value:'on',
          display: 'tool_settings_on_label'
        },
        {
          value:'off',
          display:'tool_settings_off_label'
        }
      ];
    },
    templateUrl: '/directives/onOffSelect/onOffSelect.html'
  };
});