'use strict';

appAddThisWordPress.factory('wordpress', function($q, $http, $filter) {
  var wordpress = {};

  // settingsPageId must match the backend/PHP variable in $settingsPageId in
  // the relevant feature object
  var featureConfigs = {
    globalOptions: {
      settingsPageId: 'addthis_advanced_settings',
      modeSpecific: false
    },
    followButtons: {
      settingsPageId: 'addthis_follow_buttons',
      modeSpecific: true,
      filter: 'follow'
    },
    sharingButtons: {
      settingsPageId: 'addthis_sharing_buttons',
      modeSpecific: true,
      filter: 'share'
    },
    recommendedContent: {
      settingsPageId: 'addthis_recommended_content',
      modeSpecific: true,
      filter: 'recommended'
    }
  };

  // savePrefix must match the backend/PHP variable in $ajaxSavePrefix
  var savePrefix = 'save_settings_';
  // getPrefix must match the backend/PHP variable in $ajaxGetPrefix
  var getPrefix = 'get_settings_';

  var getAjaxEndpoint = function() {
    if (window.addthis_ui.urls.ui) {
      return window.addthis_ui.urls.ajax;
    }
  };

  wordpress.widgetConfigUrl = function() {
    if (window.addthis_ui.urls.widgets) {
      return window.addthis_ui.urls.widgets;
    }
  };

  var wordpressRequest = function(action, data) {
    var deferred = $q.defer();

    var postObject = {
      action: action
    };

    if(angular.isDefined(data)) {
      if(angular.isObject(data)) {
        var dataJson = JSON.stringify(data);
        postObject.data = dataJson;
      } else {
        postObject.data = data;
      }
    }

    var postString = $filter('urlEncodeObject')(postObject);

    $http({
      method: 'POST',
      url: getAjaxEndpoint(),
      data: postString,
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Accept': '*/*'
      }
    }).then(function(response) {
      deferred.resolve(response.data);
    });

    return deferred.promise;
  };

  var getNonce = function() {
    var promise = wordpressRequest('addthis_nonce').then(function(data) {
      return data.nonce;
    });

    return promise;
  };

  var featureSetup = function(featureName, featureObject) {
    featureObject = {
      promise: false,
      done: false,
      data: false
    };

    var get = function(mode) {
      if (featureObject.promise && featureObject.done === false) {
        return featureObject.promise;
      }

      var deferred = $q.defer();
      if (featureObject.data !== false) {
        deferred.resolve(featureObject.data);
      } else {
        if (featureConfigs[featureName].modeSpecific === true &&
          angular.isDefined(mode) &&
          mode === 'AddThis'
        ) {
          featureObject.done = false;

          // get tool configs from boost
          wordpress.getBoostConfigs(true)
          .then(function(fromBoost) {

            var output = {};
            if (angular.isDefined(fromBoost) &&
                (typeof fromBoost === 'object') &&
                fromBoost !== null &&
                angular.isDefined(fromBoost.templates)
            ) {
              angular.forEach(fromBoost.templates, function(template) {
                if (angular.isDefined(template.id) &&
                  angular.isDefined(template.widgets) &&
                  template.id === '_default'
                ) {
                  output = transformIncomingBoostConfigs(template.widgets);
                }
              });
            }

            output = $filter('toolType')(
              output,
              featureConfigs[featureName].filter
            );

            featureObject.data = output;
            featureObject.done = true;
            deferred.resolve(featureObject.data);
          });
        } else {
          // get tool configs from wordpress
          var action = getPrefix + featureConfigs[featureName].settingsPageId;
          wordpressRequest(action).then(function(fromWordPress) {
            featureObject.data = fromWordPress;
            featureObject.done = true;
            deferred.resolve(featureObject.data);
          });
        }
      }

      featureObject.promise = deferred.promise;
      return featureObject.promise;
    };

    var save = function(mode, toolPco) {
      featureObject.done = false;

      if (featureConfigs[featureName].modeSpecific === true &&
          angular.isDefined(mode) &&
          mode === 'AddThis' &&
          angular.isDefined(toolPco) &&
          angular.isDefined(featureObject.data[toolPco])
      ) {
        var toolSettings = featureObject.data[toolPco];
        var boostPromise = wordpress.updateBoostConfigs(toolPco, toolSettings)
        .then(function(fromBoost) {
          var output = $filter('toolType')(
            fromBoost,
            featureConfigs[featureName].filter
          );
          featureObject.data = output;
          featureObject.done = true;
          return featureObject.data;
        });
        return boostPromise;
      } else {
        // update tool configs in wordpress
        var wordpressPromise = getNonce().then(function(nonce) {
          var action = savePrefix + featureConfigs[featureName].settingsPageId;

          var tmpFeatureObject = angular.copy(featureObject);
          angular.forEach(tmpFeatureObject.data, function(value, key) {
            if(key.search(/_json$/) !== -1) {
              try {
                tmpFeatureObject.data[key] = JSON.parse(value);
              } catch (e) {
                tmpFeatureObject.data[key] = '';
              }
            }
          });

          var data = {
            nonce: nonce,
            config: tmpFeatureObject.data
          };

          var innerPromise = wordpressRequest(action, data)
          .then(function(result) {
            featureObject.data = result;
            featureObject.done = true;
            return featureObject.data;
          });

          return innerPromise;
        });

        return wordpressPromise;
      }
    };

    featureObject.get = get;
    featureObject.save = save;

    wordpress[featureName] = {
      get: get,
      save: save
    };
  };

  var globalOptions;
  featureSetup('globalOptions', globalOptions);

  var followButtons;
  featureSetup('followButtons', followButtons);

  var sharingButtons;
  featureSetup('sharingButtons', sharingButtons);

  var recommendedContent;
  featureSetup('recommendedContent', recommendedContent);

  wordpress.defaults = function(info) {
    var result = '';

    switch (info) {
      case 'email':
        if (window.addthis_ui.defaults.email) {
          result = window.addthis_ui.defaults.email;
        }
        break;
      case 'rss':
        if (window.addthis_ui.defaults.rss) {
          result = window.addthis_ui.defaults.rss;
        }
        break;
      case 'profileName':
        if (window.addthis_ui.siteName) {
          var dirty = window.addthis_ui.siteName;
          dirty = dirty.replace(/[^a-zA-Z0-9_()\s\-]+/g, '');
          dirty = dirty.replace(/\s{2,}/g, ' ');
          result = dirty.substring(0, 255);
        }
        break;
    }

    return result;
  };

  wordpress.templateBaseUrl = function() {
    if (window.addthis_ui.urls.ui) {
      return window.addthis_ui.urls.ui;
    }

    // todo something better here to "guess" at the UI url when not provided
    return 'http://localhost:3000/ui/';
  };

  wordpress.validateAddThisProfileId = function(profileId) {
    var inputData = {
      profileId: profileId,
      plugin_version: window.addthis_ui.plugin.version,
      plugin_pco: window.addthis_ui.plugin.pco
    };

    var promise = wordpressRequest('addthis_validate_profile_id', inputData);
    return promise;
  };

  var boostConfigsObject = {
    promise: false,
    done: false,
    data: false
  };

  wordpress.getBoostConfigs = function(cache) {
    var deferred = $q.defer();

    if (boostConfigsObject.data !== false &&
      angular.isDefined(cache) &&
      cache === true
    ) {
      deferred.resolve(boostConfigsObject.data);
    } else if (boostConfigsObject.promise !== false) {
      return boostConfigsObject.promise;
    } else {
      var inputData = {
        plugin_version: window.addthis_ui.plugin.version,
        plugin_pco: window.addthis_ui.plugin.pco
      };

      wordpressRequest('addthis_get_profile_configs', inputData)
      .then(function(data) {
        if (angular.isDefined(data.data)) {
          boostConfigsObject.data = data.data;
        }
        boostConfigsObject.done = false;
        boostConfigsObject.promise = false;

        deferred.resolve(boostConfigsObject.data);
      });
    }

    boostConfigsObject.promise = deferred.promise;
    return boostConfigsObject.promise;
  };

  var transformIncomingFollowServices = function(input) {
    var output = {};
    angular.forEach(input, function(service) {
      var userType;
      if (service.service === 'facebook') {
        userType = 'user';
      } else if (typeof service.usertype === 'undefined') {
        userType = 'user';
      } else if (service.usertype === 'id') {
        userType = 'user';
      } else {
        userType = service.usertype;
      }

      output[service.service + '_' + userType] = service.id;
    });

    return output;
  };

  var transformIncomingBoostConfigs = function(input) {
    var output = {};
    angular.forEach(input, function(widget) {
      var pco = widget.id;

      if (angular.isDefined(widget.services)) {
        if (typeof widget.services === 'object') {
        // reformat services for follow buttons
          widget.services = transformIncomingFollowServices(widget.services);
        } else {
          // reformat services for share buttons
          if (widget.services.trim().length !== 0) {
            widget.services = widget.services.trim().split(',');
          } else {
            widget.services = [];
          }
        }
      }

      if (angular.isDefined(widget.followServices)) {
        if (typeof widget.followServices === 'object') {
        // reformat services for follow buttons
          widget.followServices =
            transformIncomingFollowServices(widget.followServices);
        }
      }

      if (angular.isDefined(widget.__hideOnUrls) &&
        Array.isArray(widget.__hideOnUrls)
      ) {
        widget.__hideOnUrls = widget.__hideOnUrls.join('\n');
      }

      // reformat offsets for floating tools (remove px and more)
      if (angular.isDefined(widget.offset)) {
        var reformatedOffset = {};
        var rawOffsetAmount = false;

        if (angular.isDefined(widget.offset.top)) {
          reformatedOffset.location = 'top';
          rawOffsetAmount = widget.offset.top;
        }

        if (angular.isDefined(widget.offset.bottom)) {
          reformatedOffset.location = 'bottom';
          rawOffsetAmount = widget.offset.bottom;
        }

        if (angular.isDefined(widget.offset.left)) {
          reformatedOffset.location = 'left';
          rawOffsetAmount = widget.offset.left;
        }

        if (angular.isDefined(widget.offset.right)) {
          reformatedOffset.location = 'right';
          rawOffsetAmount = widget.offset.right;
        }

        if (rawOffsetAmount) {
          var offsetParseRegex = /(\d+)(px|%)?/;
          var offsetMatches = rawOffsetAmount.match(offsetParseRegex);
          if (offsetMatches !== null) {
            if (angular.isDefined(offsetMatches[1])) {
              reformatedOffset.amount = offsetMatches[1];
            }
            if (angular.isDefined(offsetMatches[2])) {
              reformatedOffset.unit = offsetMatches[2];
            }
          }

        }

        widget.offset = reformatedOffset;
      }

      // reformat responsive (remove px)
      if (angular.isDefined(widget.responsive)) {
        if (typeof widget.responsive === 'string') {
          widget.responsive = widget.responsive.substring(
            0,
            widget.responsive.length - 2
          );
        } else if ((typeof widget.responsive === 'object') &&
          angular.isDefined(widget.responsive.maxWidth)
        ) {
          widget.responsive = widget.responsive.maxWidth.substring(
            0,
            widget.responsive.length - 2
          );
        }
      }

      // clean up elements and make it an array
      if (angular.isDefined(widget.elements)) {
        widget.elements = widget.elements.split(',');
        widget.elements.forEach(function(element, index) {
          if (element.length === 0) {
            widget.elements.splice(index, 1);
          }
        }, this);
      }

      // don't show me grey - this UI uses gray exclusively for theme
      if (angular.isDefined(widget.theme) && widget.theme === 'grey') {
        widget.theme = 'gray';
      }

      // make sure fields that are suppose to be integers are actually integers
      if (angular.isDefined(widget.offset) &&
        angular.isDefined(widget.offset.amount)
      ) {
        widget.offset.amount = parseInt(widget.offset.amount, 10);
      }

      if (angular.isDefined(widget.responsive)) {
        widget.responsive = parseInt(widget.responsive, 10);
      }

      if (angular.isDefined(widget.numrows)) {
        widget.numrows = parseInt(widget.numrows, 10);
      }

      if (angular.isDefined(widget.maxitems)) {
        widget.maxitems = parseInt(widget.maxitems, 10);
      }

      if (angular.isDefined(widget.numPreferredServices)) {
        widget.numPreferredServices = parseInt(widget.numPreferredServices, 10);
      }

      // booleans: enabled, thankyou, __hideOnHomepage, counts
      if (angular.isDefined(widget.enabled)) {
        widget.enabled = cleanUpIncomingBoostBooleans(widget.enabled);
      }

      if (angular.isDefined(widget.thankyou)) {
        widget.thankyou = cleanUpIncomingBoostBooleans(widget.thankyou);
      }

      if (angular.isDefined(widget.__hideOnHomepage)) {
        widget.__hideOnHomepage =
          cleanUpIncomingBoostBooleans(widget.__hideOnHomepage);
      }

      if (angular.isDefined(widget.counts)) {
        widget.counts = cleanUpIncomingBoostBooleans(widget.counts);
      }

      output[pco] = widget;
    });

    return output;
  };

  var transformOutboundFollowServices = function(input) {
    var output = [];
    angular.forEach(input, function(id, service) {
      if (id === '') {
        return;
      }
      var tmpService = {};
      var delimiter = '_';

      var parts = service.split(delimiter);

      if (parts.length > 1) {
        var tmpUserType = parts.pop();
        if (tmpUserType === 'id') {
          tmpService.usertype = 'user';
        } else {
          tmpService.usertype = tmpUserType;
        }
        tmpService.service = parts.join(delimiter);
      } else {
        //tmpService.usertype = 'id';
        tmpService.service = service;
      }

      tmpService.id = id;

      output.push(tmpService);
    });

    return output;
  };

  var promoteUrlPromises = [];
  var transformOutboundBoostConfig = function(toolPco, input) {
    var output = angular.copy(input);

    output.id = toolPco;

    // reformat services for follow buttons
    if (angular.isDefined(output.services) &&
      (typeof output.services === 'object')
    ) {
      if (Array.isArray(output.services)) {
        output.services = output.services.join(',');
      } else {
        output.services = transformOutboundFollowServices(output.services);
      }
    }

    if (angular.isDefined(output.followServices)) {
      if (typeof output.followServices === 'object') {
        // reformat services for follow buttons
        output.followServices =
          transformOutboundFollowServices(output.followServices);
      }
    }

    if (angular.isDefined(input.__hideOnUrls) &&
      typeof input.__hideOnUrls === 'string'
    ) {
        output.__hideOnUrls = input.__hideOnUrls.split(/\n|,/);
    }

    // reformat offsets for floating tools (remove px and more)
    if (angular.isDefined(output.offset) &&
      angular.isDefined(output.offset.location) &&
      angular.isDefined(output.offset.amount)
    ) {
      var reformatOffset = {};

      var unit = 'px';
      if (angular.isDefined(output.offset.unit)) {
        unit = output.offset.unit;
      }

      reformatOffset[output.offset.location] = output.offset.amount + unit;
      output.offset = reformatOffset;
    }

    // reformat responsive (remove px)
    if (angular.isDefined(output.responsive) &&
      (typeof output.responsive === 'number')
    ) {
      output.responsive = output.responsive + 'px';
    }

    // clean up elements and make it an array
    if (angular.isDefined(output.elements)) {
      output.elements = output.elements.join(',');
    }

    promoteUrlPromises.push(savePromotedUrl(toolPco, output.promotedUrl));
    delete output.promotedUrl;

    return output;
  };

  var savePromotedUrl = function(toolPco, url) {
    if (typeof url === 'undefined') {
      if (typeof currentPromotedUrls[toolPco] !== 'undefined') {
        return deletePromotedUrl(toolPco);
      }
    } else if ((typeof currentPromotedUrls[toolPco] === 'undefined') ||
      url !== currentPromotedUrls[toolPco]
    ) {
      return addPromotedUrl(toolPco, url);
    }

    var deferred = $q.defer();
    deferred.resolve(currentPromotedUrls);
    return deferred.promise;
  };

  var addPromotedUrl = function(toolPco, url) {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        toolPco: toolPco,
        url: url
      };

      var innerPromise = wordpressRequest('addthis_add_promoted_url', data)
      .then(function(data) {
        if (data.success === true) {
          currentPromotedUrls = data.data;
        }
        return currentPromotedUrls;
      });

      return innerPromise;
    });

    return promise;
  };

  var deletePromotedUrl = function(toolPco) {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        toolPco: toolPco
      };

      var innerPromise = wordpressRequest('addthis_delete_promoted_url', data)
      .then(function(data) {
        if (data.success === true) {
          currentPromotedUrls = data.data;
        }
        return currentPromotedUrls;
      });

      return innerPromise;
    });

    return promise;
  };

  var currentPromotedUrls = {};
  wordpress.getPromotedUrl = function() {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce
      };

      var innerPromise = wordpressRequest('addthis_get_promoted_url', data)
      .then(function(data) {
        console.log('getPromotedUrl', data);
        currentPromotedUrls = angular.copy(data.data);
        return data.data;
      });

      return innerPromise;
    });

    return promise;
  };

  var cleanUpIncomingBoostBooleans = function(value) {
    if (value === true || value === 'true' || value === 'on' || value === 1) {
      return true;
    }

    return false;
  };

  wordpress.addThisApiKeyCheck = function(profileId, apiKey) {
    var inputData = {
      profileId: profileId,
      apiKey: apiKey
    };

    var promise = wordpressRequest('addthis_check_api_key', inputData);
    return promise;
  };

  wordpress.addThisGetProfiles = function(email, password) {
    var inputData = {
      email: email,
      password: password
    };

    var promise = wordpressRequest('addthis_get_profiles', inputData);
    return promise;
  };

  wordpress.addThisRecommendedContent = function() {
    var promise = wordpressRequest('addthis_check_recommended_content');
    return promise;
  };

  wordpress.addThisCreateAccount = function(email, password, newsletter) {
    var inputData = {
      email: email,
      password: password,
      newsletter: newsletter
    };

    var promise = wordpressRequest('addthis_create_account', inputData);
    return promise;
  };

  wordpress.addThisCreateApiKey = function(email, password, profileId) {
    var inputData = {
      email: email,
      password: password,
      profileId: profileId
    };

    var promise = wordpressRequest('addthis_create_api_key', inputData);
    return promise;
  };

  wordpress.addThisCreateProfile = function(email, password, name) {
    var inputData = {
      email: email,
      password: password,
      name: name
    };

    var promise = wordpressRequest('addthis_create_profile', inputData);
    return promise;
  };

  wordpress.addThisChangeProfileType = function(profileId, apiKey) {
    var inputData = {
      profileId: profileId,
      apiKey: apiKey
    };

    var promise = wordpressRequest('addthis_change_profile_type', inputData);
    return promise;
  };

  wordpress.addThisOtherPlugins = function() {
    var promise = wordpressRequest('addthis_check_old_plugins');
    return promise;
  };

  wordpress.addThisUpdateOtherPlugin = function(source) {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        source: source
      };

      var innerPromise = wordpressRequest(
        'addthis_change_old_plugin_profile_id',
        data
      );
      return innerPromise;
    });

    return promise;
  };

  wordpress.addThisCheckLogin = function(email, password) {
    var inputData = {
      email: email,
      password: password
    };

    var promise = wordpressRequest('addthis_check_login', inputData);
    return promise;
  };

  wordpress.updateBoostConfigs = function(toolPco, toolSettings) {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        pco: toolPco,
        settings: transformOutboundBoostConfig(toolPco, toolSettings),
        plugin_version: window.addthis_ui.plugin.version,
        plugin_pco: window.addthis_ui.plugin.pco
      };

      var innerPromise = wordpressRequest('addthis_boost_update', data)
      .then(function(data) {
        var output = {};

        if (angular.isDefined(data.data) &&
            angular.isDefined(data.data.widgets)
        ) {
          output = transformIncomingBoostConfigs(data.data.widgets);
        }

        var innerInnerPromise = $q.all(promoteUrlPromises).then(function() {
          promoteUrlPromises = [];
          return output;
        });

        return  innerInnerPromise;
      });

      return innerPromise;
    });

    return promise;
  };

  wordpress.compatibleWithBoost = function() {
    var promise = getNonce().then(function(nonce) {
      var data = {
        nonce: nonce,
        plugin_version: window.addthis_ui.plugin.version,
        plugin_pco: window.addthis_ui.plugin.pco
      };

      var innerPromise = wordpressRequest('addthis_boost_compatibility', data)
      .then(function(data) {
        if (angular.isDefined(data.success) &&
            data.success === true &&
            angular.isDefined(data.compatible) &&
            data.compatible === true
        ) {
          return true;
        }

        return false;
      });

      return innerPromise;
    });

    return promise;
  };

  wordpress.isProProfile = function() {
    var promise = wordpress.getBoostConfigs(true).then(function(fromBoost) {
      if (fromBoost !== null &&
        angular.isDefined(fromBoost.subscription) &&
        angular.isDefined(fromBoost.subscription.edition)
      ) {
        if (fromBoost.subscription.edition === 'PRO') {
          return true;
        } else {
          return false;
        }
      }
    });

    return promise;
  };

  var followServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  wordpress.addThisGetFollowServices = function() {
    var deferred = $q.defer();

    if (followServicesObject.data !== false) {
      deferred.resolve(followServicesObject.data);
    } else if (followServicesObject.promise !== false) {
      return followServicesObject.promise;
    } else {
      getNonce().then(function(nonce) {
        var data = {
          nonce: nonce
        };

        wordpressRequest('addthis_get_follow_services', data)
        .then(function(data) {
          if (angular.isDefined(data.data)) {
            followServicesObject.data = data.data;
          }
          followServicesObject.done = false;
          followServicesObject.promise = false;

          deferred.resolve(followServicesObject.data);
        });
      });
    }

    followServicesObject.promise = deferred.promise;
    return followServicesObject.promise;
  };

  var shareServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  var addThisShareEndpoint = function() {
    var deferred = $q.defer();

    if (shareServicesObject.data !== false) {
      deferred.resolve(shareServicesObject.data);
    } else if (shareServicesObject.promise !== false) {
      return shareServicesObject.promise;
    } else {
      getNonce().then(function(nonce) {
        var data = {
          nonce: nonce
        };

        wordpressRequest('addthis_get_share_services', data)
        .then(function(data) {
          if (angular.isDefined(data.data)) {
            shareServicesObject.data = data.data;
          }
          shareServicesObject.done = false;
          shareServicesObject.promise = false;

          deferred.resolve(shareServicesObject.data);
        });
      });
    }

    shareServicesObject.promise = deferred.promise;
    return shareServicesObject.promise;
  };

  var addthisShareServicesObject = {
    promise: false,
    done: false,
    data: false
  };

  wordpress.addThisGetShareServices = function() {
    var deferred = $q.defer();

    if (addthisShareServicesObject.data !== false) {
      deferred.resolve(addthisShareServicesObject.data);
    } else if (addthisShareServicesObject.promise !== false) {
      return addthisShareServicesObject.promise;
    } else {
      addThisShareEndpoint().then(function(input) {
        var output = [];

        var exclude = [
          'facebook_like',
          'foursquare',
          'google_plusone',
          'pinterest',
          'addressbar',
          'googleplus'
        ];

        input.forEach(function(serviceElement) {
          if (exclude.indexOf(serviceElement.code) === -1) {
            var serviceOptionsInfo = {
              code: serviceElement.code,
              icon: serviceElement.code,
              name: serviceElement.name,
              searchString: serviceElement.code + ' ' + serviceElement.name
            };

            output.push(serviceOptionsInfo);
          }
        });

        var addThisServiceOptionInfo = {
            code: 'addthis',
            icon: 'addthis',
            name: 'AddThis',
            searchString: 'addthis more plus counter',
            index: output.length
        };

        output.push(addThisServiceOptionInfo);

        addthisShareServicesObject.data = output;
        addthisShareServicesObject.done = false;
        addthisShareServicesObject.promise = false;

        deferred.resolve(addthisShareServicesObject.data);
      });
    }

    addthisShareServicesObject.promise = deferred.promise;
    return addthisShareServicesObject.promise;
  };


  var thirdPartyShareServicesOptions = [
    {
      code: 'facebook_like',
      icon: 'facebook',
      name: 'Facebook Like',
      searchString: 'Facebook Like'
    },
    {
      code: 'facebook_send',
      icon: 'facebook',
      name: 'Facebook Send',
      searchString: 'Facebook Send Messenger'
    },
    {
      code: 'facebook_share',
      icon: 'facebook',
      name: 'Facebook Share',
      searchString: 'Facebook Share'
    },
    {
      code: 'linkedin_counter',
      icon: 'linkedin',
      name: 'LinkedIn',
      searchString: 'LinkedIn'
    },
    {
      code: 'foursquare',
      icon: 'foursquare_follow',
      name: 'Foursquare',
      searchString: 'Foursquare'
    },
    {
      code: 'stumbleupon_badge',
      icon: 'stumbleupon',
      name: 'StumbleUpon',
      searchString: 'StumbleUpon'
    },
    {
      code: 'tweet',
      icon: 'twitter',
      name: 'Twitter Tweet',
      searchString: 'Twitter Tweet'
    },
    {
      code: 'pinterest_pinit',
      icon: 'pinterest_share',
      name: 'Pinterest Pin It',
      searchString: 'Pinterest Pin It'
    },
    {
      code: 'google_plusone',
      icon: 'google_plusone_share',
      name: 'Google+1 ',
      searchString: 'Google+1 Google Plus'
    },
    {
      code: 'counter',
      icon: 'addthis',
      name: 'AddThis',
      searchString: 'addthis more plus counter'
    }
  ];

  wordpress.thirdPartyGetShareServices = function() {
    var deferred = $q.defer();
    deferred.resolve(thirdPartyShareServicesOptions);
    return deferred.promise;
  };

  var defaultToolConfigurations = {
    'esb': {
      position: 'bottom-right',
      numPreferredServices: 5,
      themeColor: undefined,
      __hideOnHomepage: false
    },
    'ist': {
      position: 'top-left-outside',
      numPreferredServices: 4,
      querySelector: '',
      borderRadius: '0%',
      buttonColor: undefined,
      iconColor: '#FFFFFF'
    },
    'cmtb': {
      position: 'bottom',
      numPreferredServices: 4,
      textColor: '#000000',
      buttonColor: undefined,
      iconColor: '#FFFFFF',
      backgroundColor: '#FFFFFF',
      __hideOnHomepage: false,
      responsive: 979,
      counts: true,
      shareCountThreshold: 10
    },
    'resh': {
      counters: 'none',
      numPreferredServices: 5,
      responsive: 979,
      elements: [
        '.addthis_responsive_sharing',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'jsc': {
      color: '#666666',
      numPreferredServices: 3,
      responsive: 979,
      label: 'SHARES',
      elements: [
        '.addthis_jumbo_share',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      countsFontSize: '60px',
      titleFontSize: '18px'
    },
    'ctbx': {
      background: '#666666',
      shape: 'square',
      size: 'large',
      counts: false,
      numPreferredServices: 5,
      theme: 'custom',
      elements: [
        '.addthis_custom_sharing',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'msd': {
      position: 'bottom',
      numPreferredServices: 4,
      services: [],
      __hideOnHomepage: false,
      responsive: 979,
      counts: true,
      shareCountThreshold: 10
    },
    'smlsh': {
      position: 'left',
      numPreferredServices: 5,
      theme: 'transparent',
      __hideOnHomepage: false,
      title: '',
      postShareTitle: 'Thanks for sharing!',
      postShareFollowMsg: 'Follow',
      postShareRecommendedMsg: 'Recommended for you',
      responsive: 979,
      thankyou: true,
      counts: true,
      offset: {
        location: 'top',
        amount: 20,
        unit: '%'
      },
      shareCountThreshold: 10
    },
    'tbx': {
      numPreferredServices: 5,
      size: 'large',
      counts: false,
      elements: [
        '.addthis_sharing_toolbox',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ],
      shareCountThreshold: 10
    },
    'scopl': {
      numPreferredServices: 5,
      thirdPartyButtons: true,
      services: [
        'facebook_like',
        'tweet',
        'pinterest_pinit',
        'google_plusone',
        'counter'
      ],
      elements: [
        '.addthis_native_toolbox',
        '.at-above-post-homepage',
        '.at-below-post-homepage',
        '.at-above-post',
        '.at-below-post',
        '.at-above-post-page',
        '.at-below-post-page',
        '.at-above-post-cat-page',
        '.at-below-post-cat-page',
        '.at-above-post-arch-page',
        '.at-below-post-arch-page'
      ]
    },
    'smlmo': {
      buttonBarPosition: 'bottom',
      buttonBarTheme: 'light',
      followServices: {},
      __hideOnHomepage: false,
      responsive: 979,
      share: 'on',
      follow: 'on'
    },
    'cflwh': {
      background: '#666666',
      shape: 'round',
      elements: ['.addthis_custom_follow'],
      theme: 'custom'
    },
    'smlfw': {
      title: 'Follow',
      theme: 'transparent',
      __hideOnHomepage: false,
      responsive: 979,
      thankyou: true,
      offset: {
        location: 'top',
        amount: 0,
        unit: 'px'
      }

    },
    'flwh': {
      title: 'Follow',
      size: 'large',
      orientation: 'horizontal',
      elements: ['.addthis_horizontal_follow_toolbox'],
      __hideOnHomepage: false,
      thankyou: true
    },
    'flwv': {
      title: 'Follow',
      size: 'large',
      orientation: 'vertical',
      elements: ['.addthis_vertical_follow_toolbox'],
      __hideOnHomepage: false,
      thankyou: true
    },
    'cod': {
      title: 'Recommended for you',
      position: 'right',
      theme: 'dark',
      promotedUrl: '',
      animationType: 'overlay',
      __hideOnHomepage: false
    },
    'tst': {
      title: 'Recommended for you',
      theme: 'light',
      __hideOnHomepage: false,
      responsive: 979,
      promotedUrl: '',
      scrollDepth: 25,
      offset: {
        location: 'right',
        amount: 0,
        unit: 'px'
      }
    },
    'jrcf': {
      __hideOnHomepage: false,
      responsive: 460,
      promotedUrl: '',
      title: 'Recommended for you',
      elements: []
    },
    'smlwn': {
      title: 'Recommended for you',
      theme: 'light',
      __hideOnHomepage: false,
      responsive: 979,
      promotedUrl: '',
      scrollDepth: 25,
      offset: {
        location: 'right',
        amount: 0,
        unit: 'px'
      }
    },
    'wnm': {
      title: 'Recommended for you',
      theme: 'light',
      promotedUrl: '',
      __hideOnHomepage: false,
      scrollDepth: 25
    },
    'smlre': {
      title: 'Recommended for you',
      theme: 'light',
      numrows: 1,
      maxitems: 3,
      promotedUrl: '',
      __hideOnHomepage: false
    },
    'smlrebh': {
      title: 'Recommended for you',
      theme: 'transparent',
      numrows: 1,
      maxitems: 4,
      promotedUrl: '',
      orientation: 'horizontal',
      elements: ['.addthis_recommended_horizontal']
    },
    'smlrebv': {
      title: 'Recommended for you',
      theme: 'transparent',
      maxitems: 4,
      elements: ['.addthis_recommended_vertical'],
      promotedUrl: '',
      orientation: 'vertical'
    }
  };

  wordpress.addDefaultToolConfigurations = function(toolPco, inputConfigs) {
    var defaultConfigs = {};
    if (typeof defaultToolConfigurations[toolPco] !== 'undefined') {
      defaultConfigs = angular.copy(defaultToolConfigurations[toolPco]);
    }

    if (typeof inputConfigs === 'undefined') {
      inputConfigs = {};
    }

    angular.forEach(defaultConfigs, function(value, key) {
      if (typeof inputConfigs[key] === 'undefined') {
        inputConfigs[key] = value;
      }
    });

    return inputConfigs;
  };

  return wordpress;
});