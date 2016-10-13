<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2016 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

require_once 'AddThisRegistrationFeature.php';
require_once 'AddThisGlobalOptionsFeature.php';
require_once 'AddThisFollowButtonsFeature.php';
require_once 'AddThisSharingButtonsFeature.php';
require_once 'AddThisRecommendedContentFeature.php';
require_once 'AddThisTool.php';

if (!class_exists('AddThisPlugin')) {
    /**
     * AddThis' root parent class for all its plugins
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Plugin
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisPlugin
    {
        protected $enabledFeatures = array();
        protected $registrationObject = null;
        protected $globalOptionsObject = null;
        protected $followButtonsObject = null;
        protected $sharingButtonsObject = null;
        protected $recommendedContentObject = null;

        protected $registrationStatus = true;
        protected $globalOptionsStatus = true;
        protected $followButtonsStatus = false;
        protected $sharingButtonsStatus = false;
        protected $recommendedContentStatus = false;

        protected $baseName = null;
        protected $settingsLinkObject = 'globalOptionsObject';
        protected static $cmsName = 'WordPress';
        // adminJavaScriptAction needs to match here and in AddThisFeature
        protected $adminJavaScriptAction = 'addthis_admin_variables';

        // the order of the array below matters for JavaScript variable
        // precidence make a protected variable that matches objectName above
        // require the files with the objectName classes at the top of this file
        protected $features = array(
            'globalOptions'  => array(
                'name'         => 'Global Options',
                'objectName'   => 'AddThisGlobalOptionsFeature',
            ),
            'registration'  => array(
                'name'         => 'Registration',
                'objectName'   => 'AddThisRegistrationFeature',
            ),
            'followButtons'  => array(
                'name'         => 'Follow Buttons',
                'objectName'   => 'AddThisFollowButtonsFeature',
            ),
            'sharingButtons' => array(
                'name'         => 'Sharing Buttons',
                'objectName'   => 'AddThisSharingButtonsFeature',
            ),
            'recommendedContent' => array(
                'name'         => 'Recommended Content',
                'objectName'   => 'AddThisRecommendedContentFeature',
            ),
        );

        /**
         * The constructor.
         *
         * @param string $baseName the base name for this plugin
         *
         * @return null
         */
        public function __construct($baseName)
        {
            $this->baseName = $baseName;
        }

        /**
         * Returns the  version of this plugin
         *
         * @return string
         */
        public function getVersion()
        {
            return $this->version;
        }

        /**
         * Returns the product version of this plugin for lojson params
         *
         * @return string
         */
        public function getProductVersion()
        {
            $productVersion = $this->productPrefix . '-' . $this->getVersion();
            return $productVersion;
        }

        /**
         * Adds a a filter for the plugin_action_links area for this plugin, and
         * adds the main feature's addSettingsLinkToPlugin function as a filter
         * onto it. Essentially, this adds the settings link to the plugin
         * listing page.
         *
         * @return null
         */
        protected function addSettingsLinkToPlugin()
        {
            $callback = array($this, 'settingLinksFilter');

            $filterName = 'plugin_action_links_'.$this->baseName;

            if ($this->validateCallback($callback)) {
                add_filter($filterName, $callback);
            }
        }

        /**
         * This must be public as it's used in a callback for
         * plugin_action_links_$plugin filter
         *
         * Adds a link to the settings and registration pages for this plugin
         * the WordPress plugin listing
         *
         * @param string[] $links an array of strings of HTML anchor tags
         *
         * @return string[] the input with added links
         */
        public function settingLinksFilter($links)
        {
            $variableName = $this->settingsLinkObject;

            if (is_object($this->$variableName)) {
                $link = $this->$variableName->addSettingsLinkToPlugin();
                array_push($links, $link);
            }

            if (is_object($this->registrationObject)) {
                $link = $this->registrationObject->addSettingsLinkToPlugin();
                array_push($links, $link);
            }

            return $links;
        }

        /**
         * Marks each feature enabled by this plugin as disabled -- if another
         * plugin also enables any of these feature, then it'll get enabled the
         * next time that plugin bootraps
         *
         * @return null
         */
        public function deactivate()
        {
            $gooConfigs = $this->globalOptionsObject->getConfigs();

            foreach ($this->features as $feature => $info) {
                $objectVariable = $feature . 'Object';
                $enabledVariable = $feature . 'Status';
                $enabledByPlugin = $this->$enabledVariable;

                if ($enabledByPlugin && $feature != 'globalOptions') {
                    $enabledField = $this->$objectVariable->globalEnabledField;
                    $jsonField = $this->$objectVariable->globalLayersJsonField;

                    $globalOptionsConfigs[$enabledField] = false;
                    $globalOptionsConfigs[$jsonField] = '';
                }
            }

            $this->globalOptionsObject->saveConfigs($globalOptionsConfigs);
        }

        /**
         * This must be public as it's called from bootstrap.php
         *
         * This bootstraps this plugin into WordPress, including adding our
         * JavaScript and CSS onto relevant pages and calling the bootstrap
         * function for every enabled feature
         *
         * @return null
         */
        public function bootstrap()
        {
            $goo = new $this->features['globalOptions']['objectName']();
            $this->globalOptionsObject = $goo;

            $reg = new $this->features['registration']['objectName']($goo);
            $this->registrationObject = $reg;

            $reg->bootstrap();
            $goo->bootstrap();

            $gooConfigs = $goo->getConfigs();
            $saveGooConfigs = false;

            /**
             * We don't want to run updateProfileEdition unnecessarily as it
             * talks to AddThis and blocks futher action until AddThis responds
             * (to check the profile edition). It will only run here if this
             * is an admin page that might be setting up widgets. We need this
             * when registering widgets to know which ones to skip. See
             * AddThisFeature::registerWidgets for details on why we do this
             * here.
             */
            if (is_admin()
                && !empty($_SERVER['REQUEST_URI'])
                && (substr($_SERVER['REQUEST_URI'], -11) == 'widgets.php'
                    || strpos($_SERVER['REQUEST_URI'], 'customize.php'))
            ) {
                $updatedProfileEdition = $goo->updateProfileEdition();
                if ($updatedProfileEdition) {
                    $gooConfigs = $goo->getConfigs();
                    $saveGooConfigs = true;
                }
            }

            foreach ($this->features as $feature => $info) {
                $objectVariable = $feature . 'Object';
                $objectName = $info['objectName'];
                $enabledVariable = $feature . 'Status';

                if ($feature != 'globalOptions' && $feature != 'registration') {
                    $featureObject = new $objectName($goo);
                    $this->$objectVariable = $featureObject;

                    // Does this plugin enable this feature? If so, bootstrap
                    if ($this->$enabledVariable) {
                        $this->enabledFeatures[] = $feature;
                        $this->$objectVariable->bootstrap();

                        // Does the plugin know it's enabled? If not, fix it.
                        $pluginKnowsItIsEnabled = $this->$objectVariable->isEnabled();
                        if (!$pluginKnowsItIsEnabled) {
                            $field = $this->$objectVariable->globalEnabledField;
                            $gooConfigs[$field] = true;
                            $saveGooConfigs = true;
                        }
                    } else {
                        $this->$objectVariable->getConfigs();
                    }
                }
            }

            if (!isset($gooConfigs['addthis_plugin_controls'])) {
                if ($gooConfigs['follow_buttons_feature_enabled'] === true
                    || $gooConfigs['recommended_content_feature_enabled'] === true
                    || $gooConfigs['sharing_buttons_feature_enabled'] === true
                    || $gooConfigs['trending_content_feature_enabled'] === true
                ) {
                    $gooConfigs['addthis_plugin_controls'] = 'WordPress';
                } else {
                    $gooConfigs['addthis_plugin_controls'] = 'AddThis';
                }

                $saveGooConfigs = true;
            }

            if ($saveGooConfigs) {
                $goo->saveConfigs($gooConfigs);
            }

            // Add a link to the main settings page & the registration page
            $this->addSettingsLinkToPlugin();

            add_filter(
                'language_attributes',
                array($this, 'htmlNameSpacesAttributes')
            );

            if (is_admin()) {
                // load our language files -- we only use them on admin pages
                add_action(
                    'plugins_loaded',
                    array($this, 'loadTextDomain')
                );

                // addthis_share, addthis_config, addthis_layers and
                // addthis_plugin_info for all public pages
                add_action(
                    'wp_ajax_'.$this->globalOptionsObject->publicJavaScriptAction,
                    array($this, 'printJavaScriptForGlobalVariables')
                );
                add_action(
                    'wp_ajax_nopriv_'.$this->globalOptionsObject->publicJavaScriptAction,
                    array($this, 'printJavaScriptForGlobalVariables')
                );

                // make JavaScript file to ui relevant variables
                add_action(
                    'wp_ajax_'.$this->adminJavaScriptAction,
                    array($this, 'printJavascriptForAdminUi')
                );
            } else {
                $this->addScripts();
            }

            register_deactivation_hook(
                $this->baseName,
                array($this, 'deactivate')
            );
        }

        /**
         * This must be public as it's used for a callback for the
         * plugins_loaded action
         *
         * Loads our language files needed in PHP for this plugin
         *
         * @return null
         */
        public function loadTextDomain()
        {
            $path = '/' . $this->globalOptionsObject->getPluginFolder() . '/frontend/build/l10n';
            load_plugin_textdomain(AddThisFeature::$l10n_domain, false, $path);
        }

        /**
         * Setup out scripts to enqueue (if async is off), adds an action that
         * echos our script onto page if async is turned on, and adds an action
         * to enqueue our styles late. For all public pages.
         *
         * @return null
         */
        protected function addScripts()
        {
            $gooConfigs = $this->globalOptionsObject->getConfigs();

            if (!empty($gooConfigs['script_location'])
                && $gooConfigs['script_location'] == 'footer'
            ) {
                $pageScriptHook = 'wp_footer';
            } else {
                $pageScriptHook = 'wp_head';
            }

            if (!empty($gooConfigs['addthis_asynchronous_loading'])) {
                add_action(
                    $pageScriptHook,
                    array($this, 'printAddThisWidgetScript'),
                    19
                );
            } else {
                add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
            }

            // we want this to get enqueued after the theme css, thus the 18
            add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'), 18);
        }


        /**
         * This must be public as it's used for a callback in the
         * wp_enqueue_scripts action
         *
         * Enqueues our scripts the correct way when we don't have to do hacky
         * async loading stuff
         *
         * @return null
         */
        public function enqueueScripts()
        {
            $gooConfigs = $this->globalOptionsObject->getConfigs();

            if (!empty($gooConfigs['script_location'])
                && $gooConfigs['script_location'] == 'footer'
            ) {
                $enqueueInFooter = true;
            } else {
                $enqueueInFooter = false;
            }

            $bootstrapSettingsUrl = admin_url('admin-ajax.php') . '?action='.$this->globalOptionsObject->publicJavaScriptAction;
            wp_enqueue_script(
                'addthis_global_options',
                $bootstrapSettingsUrl,
                array(),
                false,
                $enqueueInFooter
            );

            $addThisWidgetUrl = $this->globalOptionsObject->getAddThisWidgetJavaScriptUrl();
            $dependencies = array('addthis_global_options');
            wp_enqueue_script(
                'addthis_widget',
                $addThisWidgetUrl,
                $dependencies,
                false,
                $enqueueInFooter
            );
        }

        /**
         * This must be public as it's used for a callback in the
         * wp_enqueue_scripts action
         *
         * Enqueues our styles super late, in the hopes that nothing else will
         * enquque after us, and we win! This is done so that our CSS can win
         * out over template CSS more often. For all public pages.
         *
         * @return null
         */
        public function enqueueStyles()
        {
            $gooConfigs = $this->globalOptionsObject->getConfigs();

            if (!empty($gooConfigs['script_location'])
                && $gooConfigs['script_location'] == 'footer'
            ) {
                $enqueueInFooter = true;
            } else {
                $enqueueInFooter = false;
            }

            $cssRoot = $this->globalOptionsObject->getSettingsUiBaseUrl() . 'build/';
            wp_enqueue_style(
                'addthis_all_pages',
                $cssRoot . 'addthis_wordpress_public.min.css',
                array(),
                false
            );
        }

        /**
         * This must be public as it's used for a callback in the filter
         * language_attributes
         *
         * Checks for the Facebook Graph namespace and the AddThis namespace,
         * and adds it if it doesn't already exist in the input string.
         *
         * @param string $input the string of attributes to be added the pages
         * HTML tag
         *
         * @return string the string of attributes to be added to the pages HTML
         * tag with Facebook and AddThis names spaces added if thye wheren't
         * there already
         */
        public function htmlNameSpacesAttributes($input)
        {
            $gooConfigs = $this->globalOptionsObject->getConfigs();
            if (empty($gooConfigs['xmlns_attrs'])) {
                return $input;
            }

            $output = trim($input);

            $attrs = array(
                array(
                    'attr' => 'xmlns:fb',
                    'value' => 'http://ogp.me/ns/fb#',
                ),
                array(
                    'attr' => 'xmlns:addthis',
                    'value' => 'http://www.addthis.com/help/api-spec',
                ),
            );

            foreach ($attrs as $info) {
                if (strpos($input, $info['attr']) === false) {
                    $output .= ' ' . $info['attr']
                        . '="' . $info['value'] . '"';
                }
            }

            $output .= ' ';
            return $output;
        }

        /**
         * Prints the JavaScript that should be used to set addthis_share,
         * addthis_config, addthis_plugin_info, as well as the params to use
         * when loading addthis.layers
         *
         * @return null
         */
        public function printJavaScriptForGlobalVariables()
        {
            if (is_feed()) {
                return null;
            }

            header('Content-type: application/x-javascript');

            $javaScript = $this->getJavascriptForGlobalVariables();

            echo $javaScript;
            die();
        }

        /**
         * Returns a string of JavaScript that should be used to set
         * addthis_share, addthis_config, addthis_plugin_info, as well as the
         * params to use when loading addthis.layers
         *
         * @return string
         */
        public function getJavascriptForGlobalVariables()
        {
            $share = array();
            $config = array();
            $layers = array();
            $layersTools = array();
            $pluginInfo = $this->getAddThisPluginInfo();

            $gooSettings = $this->globalOptionsObject->getConfigs();
            $updateGlobalOptionsSettings = false;

            reset($this->features);
            foreach ($this->features as $feature => $info) {
                $objectVariable = $feature . 'Object';
                $featureObject = $this->$objectVariable;

                if ($featureObject->isEnabled()) {
                    $featureShare = $featureObject->getAddThisShare();
                    $share = array_replace_recursive($share, $featureShare);

                    $featureConfig = $featureObject->getAddThisConfig();
                    $config = array_replace_recursive($config, $featureConfig);

                    $featureLayers = $featureObject->getAddThisLayers();
                    if ($this->globalOptionsObject->inAnonymousMode()) {
                        $layers = array_replace_recursive($layers, $featureLayers);
                    }

                    $featureLayersTools = $featureObject->getAddThisLayersTools();
                    if ($this->globalOptionsObject->inAnonymousMode()) {
                        $layersTools = array_merge($layersTools, $featureLayersTools);
                    }

                    // save a layers json string for the sharing buttons plugin
                    if (!empty($featureObject->globalLayersJsonField)) {
                        $new = json_encode((object)$featureLayers);
                        $field = $featureObject->globalLayersJsonField;

                        if (isset($gooSettings[$field])) {
                            $old = $gooSettings[$field];
                        }

                        if ((empty($old) && !empty($featureLayers))
                            || (!empty($old) && $old != $new)
                        ) {
                            $gooSettings[$field] = $new;
                            $updateGlobalOptionsSettings = true;
                        }
                    }
                }
            }

            // save a layers json string for the sharing buttons plugin
            if ($updateGlobalOptionsSettings) {
                $this->globalOptionsObject->saveConfigs($gooSettings);
            }

            if (!empty($gooSettings['addthis_share_json'])) {
                $shareFromSettings = json_decode($gooSettings['addthis_share_json'], true);
                $share = array_replace_recursive($share, $shareFromSettings);
            }
            $share = apply_filters('addthis_share_array', $share);
            $shareJson = json_encode((object)$share);
            $shareJson = apply_filters('addthis_share_json', $shareJson, $share);

            if (!empty($gooSettings['addthis_config_json'])) {
                $configFromSettings = json_decode($gooSettings['addthis_config_json'], true);
                $config = array_replace_recursive($config, $configFromSettings);
            }
            $config = apply_filters('addthis_config_array', $config);
            $configJson = json_encode((object)$config);
            $configJson = apply_filters('addthis_config_json', $configJson, $config);

            if (!empty($gooSettings['addthis_layers_json'])) {
                $layersFromSettings = json_decode($gooSettings['addthis_layers_json'], true);
                $layers = array_replace_recursive($layers, $layersFromSettings);
            }
            $layers = apply_filters('addthis_layers_array', $layers);
            $layersJson = json_encode((object)$layers);
            $layersJson = apply_filters('addthis_layers_json', $layersJson, $layers);

            $layersToolsJson = json_encode((array)$layersTools);
            $layersToolsAlreadyDefined = '';
            foreach ($layersTools as $toolLayersConfig) {
                $toolLayersConfig = json_encode((object)$toolLayersConfig);
                $layersToolsAlreadyDefined .= 'window.addthis_layers_tools.push('.$toolLayersConfig.'); ';
            }

            $pluginInfoJson = json_encode((object)$pluginInfo);

            $javaScript = '
                if (window.addthis_product === undefined) {
                    window.addthis_product = "' . $this->productPrefix . '";
                }

                if (window.wp_product_version === undefined) {
                    window.wp_product_version = "' . $this->getProductVersion() . '";
                }

                if (window.wp_blog_version === undefined) {
                    window.wp_blog_version = "' . $this->getCmsVersion() . '";
                }

                if (window.addthis_share === undefined) {
                    window.addthis_share = ' . $shareJson . ';
                }

                if (window.addthis_config === undefined) {
                    window.addthis_config = ' . $configJson . ';
                }

                if (window.addthis_layers === undefined) {
                    window.addthis_layers = ' . $layersJson . ';
                }

                if (window.addthis_layers_tools === undefined) {
                    window.addthis_layers_tools = ' . $layersToolsJson . ';
                } else {
                    '.$layersToolsAlreadyDefined.'
                }


                if (window.addthis_plugin_info === undefined) {
                    window.addthis_plugin_info = ' . $pluginInfoJson . ';
                }

                '.$this->getJavaScriptToLoadTools().'
            ';

            $javaScript = apply_filters('addthis_config_javascript', $javaScript);
            return $javaScript;
        }

        /**
         * Returns a string of JavaScript that can be used to bootstrap tools
         *
         * @return string JavaScript for bootstraping AddThis tools
         */
        public function getJavaScriptToLoadTools()
        {
            $gooSettings = $this->globalOptionsObject->getConfigs();
            if (empty($gooSettings['ajax_support'])) {
                $javaScript = '
                    (function() {
                      var first_load_interval_id = setInterval(function () {
                        if (typeof window.addthis !== \'undefined\') {
                          window.clearInterval(first_load_interval_id);
                          window.addthis.layers(window.addthis_layers);
                          for (i = 0; i < window.addthis_layers_tools.length; i++) {
                            window.addthis.layers(window.addthis_layers_tools[i]);
                          }
                        }
                     },1000)
                    }());
                ';
            } else {
                $javaScript = '
                    (function() {
                      var new_tools_timeout = false;

                      var refresh_tools = function() {
                        new_tools_timeout = false;
                        addthis.layers.refresh();
                      };

                      var first_load_check = function () {
                        if (typeof window.addthis !== \'undefined\') {
                          window.clearInterval(first_load_interval_id);
                          window.addthis.layers(window.addthis_layers);
                          for (i = 0; i < window.addthis_layers_tools.length; i++) {
                            window.addthis.layers(window.addthis_layers_tools[i]);
                          }

                          window.atnt = function() {
                            if (new_tools_timeout !== false) {
                              window.clearTimeout(new_tools_timeout);
                            }
                            new_tools_timeout = window.setTimeout(refresh_tools, 15);
                          };
                        }
                      };

                      var first_load_interval_id = window.setInterval(first_load_check, 1000);
                    }());
                ';
            }

            return $javaScript;
        }

        /**
         * Creates plugin specific settings for the JavaScript variable
         * addthis_plugin_info
         *
         * @return array an associative array
         */
        public function getAddThisPluginInfo()
        {
            $pluginInfo = array();

            $pluginInfo = array();
            $pluginInfo['info_status'] = 'enabled';
            $pluginInfo['cms_name'] = $this->getCmsName();
            $pluginInfo['cms_version'] = $this->getCmsVersion();
            $pluginInfo['plugin_name'] = $this->name;
            $pluginInfo['plugin_version'] = $this->getVersion();
            if ($this->globalOptionsObject->inAnonymousMode()) {
                $pluginInfo['plugin_mode'] = $this->getCmsName();
            } else {
                $pluginInfo['plugin_mode'] = 'AddThis';
            }
            $pluginInfo['anonymous_profile_id'] = $this->globalOptionsObject->getAnonymousProfileId();

            if ($this->globalOptionsObject->checkForEditPermissions(false)) {
                $pluginInfo['php_version'] = phpversion();
                $pluginInfo['cms_version'] = $this->getCmsVersion();
            }

            // post specific stuff that requires wp_query
            global $wp_query;
            if (isset($wp_query)) {
                $pluginInfo['page_info']['template'] = AddThisTool::currentTemplateType();
                if (isset($wp_query->query_vars['post_type'])) {
                    $pluginInfo['page_info']['post_type'] = $wp_query->query_vars['post_type'];
                }
            }

            // post specific meta box selection
            global $post;
            if (is_object($post)
                && ($post instanceof WP_Post)
                && !empty($post->ID)
            ) {
                $at_flag = get_post_meta($post->ID, '_at_widget', true);
                if ($at_flag === '0') {
                    $pluginInfo['sharing_enabled_on_post_via_metabox'] = false;
                } else {
                    $pluginInfo['sharing_enabled_on_post_via_metabox'] = true;
                }
            }

            return $pluginInfo;
        }

        /**
         * Prints the JavaScript that should be used to include
         * addthis_widget.js on the page.
         *
         * @return null
         */
        public function printAddThisWidgetScript()
        {
            if (is_feed()) {
                return null;
            }

            $configs = $this->globalOptionsObject->getConfigs();
            $addthisWidgetUrl = $this->globalOptionsObject->getAddThisWidgetJavaScriptUrl();

            $script = '
                <script
                    data-cfasync="false"
                    type="text/javascript"
                >
                '.$this->getJavascriptForGlobalVariables().'
                </script>
                <script
                    data-cfasync="false"
                    type="text/javascript"
                    src="' . $addthisWidgetUrl . '"
                    async="async"
                ></script>
            ';

            echo $script;
        }

        /**
         * The profile ID that should be used when loading addthis_widget.js
         *
         * @return string a profile ID
         */
        public function getUsableProfileId()
        {
            $profileId = $this->globalOptionsObject ->getUsableProfileId();
            return $profileId;
        }

        /**
         * Validates that the WordPress callback passed is valid.
         *
         * @param string|array $callback The name of a global function as a
         * string, or an array with the first item being an object, and the
         * second being a string naming a function in that object.
         *
         * @return boolean true for valid, false otherwise
         */
        public static function validateCallback($callback)
        {
            if (empty($callback[0])) {
                return false;
            }

            if (empty($callback[1])) {
                // check if string
                $methodName = $callback;
                if (!is_string($methodName)) {
                    return false;
                }

                if (!function_exists($methodName)) {
                    return false;
                }

                return true;
            }

            $object = $callback[0];
            $methodName = $callback[1];

            if (!is_object($object)) {
                return false;
            } elseif (!method_exists($object, $methodName)) {
                return false;
            }

            return true;
        }

        /**
         * The name of the content management system this plugin runs within
         *
         * @return string
         */
        private static function getCmsName()
        {
            return self::$cmsName;
        }

        /**
         * The full version number of the CMS (ie. 4.2.2, not 4.2)
         *
         * @return string
         */
        private static function getCmsVersion()
        {
            $version = get_bloginfo('version');
            return $version;
        }

        /**
         * The major and minor version of the CMS (ie. 4.2, not 4.2.2)
         *
         * @return string
         */
        private static function getCmsMinorVersion()
        {
            $stringVersion = substr(self::getCmsVersion(), 0, 3);
            $version = (float)$stringVersion;
            return $version;
        }


        /**
         * This must be public as it's used in a callback for add_action
         *
         * Prints JavaScript to make site specific variables available to the UI
         *
         * @return null
         */
        public function printJavascriptForAdminUi()
        {
            $this->globalOptionsObject->checkForEditPermissions(true);

            header('Content-type: application/x-javascript');

            $current_user = wp_get_current_user();
            $config = array(
                'ignore_server_config' => true,
            );

            $ui = array(
                'defaults'   => array(
                    'rss'       => get_bloginfo('rss2_url'),
                    'email'     => $current_user->user_email,
                    'domain'    => $this->globalOptionsObject->getSiteDomain(),
                ),
                'urls'       => array(
                    'home'     => home_url(),
                    'admin'    => admin_url(),
                    'ajax'     => admin_url('admin-ajax.php'),
                    'widgets'  => admin_url('widgets.php'),
                    'ui'       => $this->globalOptionsObject->getSettingsUiBaseUrl(),
                    'settings' => $this->globalOptionsObject->getSettingsPageUrl(),
                ),
                'plugin'     => array(
                    'slug'     => $this->pluginSlug,
                    'pco'      => $this->productPrefix,
                    'version'  => $this->getVersion(),
                ),
                'siteName'   => get_bloginfo('name'),
                'language'   => get_bloginfo('language'),
                'locale'     => get_locale(),
            );

            $configJson = json_encode((object)$config);
            $uiJson = json_encode((object)$ui);

            $javaScript = '
                window.addthis_config = ' . $configJson . ';
                window.addthis_ui = ' . $uiJson . ';
            ';

            echo $javaScript;
            die();
        }
    }
}