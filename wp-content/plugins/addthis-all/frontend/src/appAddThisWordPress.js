'use strict';

// Declare app level module which depends on views, and components
var appAddThisWordPress = angular.module(
  'appAddThisWordPress',
  [
    'addthis',
    'cfp.hotkeys',
    'ngAria',
    'pascalprecht.translate',
    'ui.router'
  ]
);

appAddThisWordPress.config(function($sceDelegateProvider) {
  $sceDelegateProvider.resourceUrlWhitelist([
    'self',
    'https://www.addthis.com/darkseid/**',
    'https://cache.addthiscdn.com/services/**',
	  //include local and internal URLs for development purposes
    'http://localhost:3000/**',
    'http://www-test.addthis.com/darkseid/**',
    'http://www-dev.addthis.com/darkseid/**',
    'http://www-local.addthis.com/darkseid/**'
  ]);
});

appAddThisWordPress.config(function($translateProvider) {
  if ((typeof window.addthis_ui !== undefined) &&
    (typeof window.addthis_ui.locale !== undefined)
  ) {
      $translateProvider.preferredLanguage(window.addthis_ui.locale);
  } else {
      $translateProvider.preferredLanguage('en_US');
  }

  $translateProvider.fallbackLanguage(['en_US']);

  $translateProvider.useStaticFilesLoader({
    prefix: window.addthis_ui.urls.ui + 'build/l10n/addthis-frontend-',
    suffix: '.json'
  });

  $translateProvider.useSanitizeValueStrategy(null);
});

appAddThisWordPress.config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise(function($injector, $location){
    var state = 'registration';
    var wordpressPageRegex = /\?page=([a-z0-9_]+)/i;
    var matches = $location.absUrl().match(wordpressPageRegex);
    if (matches !== null && typeof matches[1] !== 'undefined') {
      var wpPageId = matches[1];
      if (wpPageId === 'addthis_registration') {
        state = 'registration';
      } else if (wpPageId === 'addthis_advanced_settings') {
        state = 'advanced';
      } else if (wpPageId === 'addthis_follow_buttons') {
        state = 'follow';
      } else if (wpPageId === 'addthis_sharing_buttons') {
        state = 'share';
      } else if (wpPageId === 'addthis_recommended_content') {
        state = 'recommend';
      } else {
        state = 'oops';
      }
    }
    return state;
  });

  $stateProvider
  .state('registration', {
    url: '/registration',
    templateUrl: '/features/Registration/RegistrationParent.html'
  })
  .state('registration.state', {
    url: '/:registrationState',
    templateUrl: '/features/Registration/RegistrationParent.html'
  })
  .state('advanced', {
    url: '/advanced',
    templateUrl: '/features/AdvancedSettings/AdvancedSettingsParent.html'
  })
  .state('follow', {
    url: '/follow',
    templateUrl:
    '/features/FollowButtonSettings/FollowButtonSettingsParent.html'
  })
  .state('follow.pco', {
    url: '/pco/:toolPco',
    templateUrl:
    '/features/FollowButtonSettings/FollowButtonSettingsParent.html'
  })
  .state('follow_conflict', {
    url: '/follow_conflict/:toolPco',
    templateUrl: '/features/FollowButtonConflict/FollowButtonConflict.html'
  })
  .state('share', {
    url: '/share',
    templateUrl: '/features/ShareButtonSettings/ShareButtonSettingsParent.html'
  })
  .state('share.pco', {
    url: '/pco/:toolPco',
    templateUrl: '/features/ShareButtonSettings/ShareButtonSettingsParent.html'
  })
  .state('recommend', {
    url: '/recommend',
    templateUrl:
    '/features/RecommendedContentSettings/RecommendedContentSettingsParent.html'
  })
  .state('recommend.pco', {
    url: '/pco/:toolPco',
    templateUrl:
    '/features/RecommendedContentSettings/RecommendedContentSettingsParent.html'
  })
  .state('oops', {
    url: '/oops',
    templateUrl: '/features/OopsSettings/OopsSettings.html'
  });
});