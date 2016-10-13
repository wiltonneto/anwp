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

require_once 'AddThisGlobalOptionsFeature.php';

if (!class_exists('AddThisTool')) {
    /**
     * AddThis' root parent class for all its tools. These objects know how to
     * render specific tools onto pages and how to store their configurations.
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisTool
    {
        public $layersClass = 'addthis_you_forgot_to_set_the_addthis_layers_class';
        public $prettyName = 'You forgot to set a pretty name';

        public $edition = 'pro';
        public $anonymousSupport = false;
        public $inline = false;
        public $addedDefaultValue = false;
        protected $defaultConfigs = array(
            'enabled' => false,
        );
        public $contentFiltersEnabled = false;

        protected $globalOptionsClassName = 'AddThisGlobalOptionsFeature';
        protected $globalOptionsObject = null;
        protected $featureClassName = 'YouDidNotSetAFeatureClass';
        protected $featureObject = null;
        protected $featureConfigs = null;
        protected $globalOptionsConfigs = null;
        protected $toolConfigs = null;

        public $widgetClassName = null;
        public $widgetBaseId = 'addthis_you_forgot_to_set_the_base_id_widget';
        public $widgetName = 'You forgot to name your widget';
        public $widgetDescription = 'You forgot to describe your widget';
        public $defaultWidgetTitle = 'No default title set for this widget';

        public $shortCode = 'addthis_you_forgot_to_set_the_short_code';

        public $defaultTheme = 'transparent';

        /**
         * The constructor.
         *
         * @param object                      $featureObject       the object
         * for this tool's feature family. Optional.
         * @param AddThisGlobalOptionsFeature $globalOptionsObject the object
         * for the Global Options feature. Optional.
         *
         * @return null
         */
        public function __construct(
            $featureObject = null,
            $globalOptionsObject = null
        ) {
            if (is_object($globalOptionsObject)) {
                $this->globalOptionsObject = $globalOptionsObject;
            }

            if (is_object($featureObject)) {
                $this->featureObject = $featureObject;
            }
        }

        /**
         * Returns this tool's feature object (mostly used to get database
         * settings). If the object isn't already populated in a variable in
         * this class, it will attempt to create it.
         *
         * @return object|null
         */
        public function getFeatureObject()
        {
            if (!is_object($this->featureObject)) {
                if (class_exists($this->featureClassName)) {
                    $goo = $this->getGlobalOptionsObject();
                    $this->featureObject = new $this->featureClassName($goo);
                    $this->featureObject->getConfigs();
                } else {
                    error_log(__METHOD__ . ' class ' . $this->featureClassName . ' does not exists.');
                }
            }

            return $this->featureObject;
        }

        /**
         * Returns the Global Options feature object. If the object isn't
         * already populated in a variable in this class, it will attempt to
         * create it.
         *
         * @return AddThisGlobalOptionsFeature|null an object of class
         * AddThisGlobalOptionsFeature or null on failure
         */
        public function getGlobalOptionsObject()
        {
            if (!is_object($this->globalOptionsObject)) {
                if (class_exists($this->globalOptionsClassName)) {
                    $goo = new $this->globalOptionsClassName();
                    $goo->getConfigs();
                    $this->globalOptionsObject = $goo;
                } else {
                    error_log(__METHOD__ . ' class ' . $this->globalOptionsClassName . ' does not exists.');
                }
            }

            return $this->globalOptionsObject;
        }

        /**
         * Retrieves the settings for Global Options
         *
         * @return array an associative array
         */
        public function getGlobalOptionsConfigs()
        {
            if (is_null($this->globalOptionsConfigs)) {
                $this->getGlobalOptionsObject();

                if (is_object($this->globalOptionsObject)) {
                    $configs = $this->globalOptionsObject->getConfigs();
                    $this->globalOptionsConfigs = $configs;
                }
            }

            return $this->globalOptionsConfigs;
        }

        /**
         * Retrieves the settings for this feature family
         *
         * @return array an associative array
         */
        public function getFeatureConfigs()
        {
            if (is_null($this->featureConfigs)) {
                $this->getFeatureObject();

                if (is_object($this->featureObject)) {
                    $this->featureConfigs = $this->featureObject->getConfigs();
                }
            }

            return $this->featureConfigs;
        }

        /**
         * Retrieves the settings for this particular tool
         *
         * @return array an associative array
         */
        public function getToolConfigs()
        {
            $this->getFeatureConfigs();

            if (isset($this->settingsSubVariableName)
                && isset($this->featureConfigs[$this->settingsSubVariableName])
            ) {
                $toolKey = $this->settingsSubVariableName;
                $this->toolConfigs = $this->featureConfigs[$toolKey];
            }

            return $this->toolConfigs;
        }

        /**
         * Checks if this tool has been enabled by the user.
         *
         * @return boolean true for enabled, false for disabled
         */
        public function isEnabled()
        {
            $this->getToolConfigs();

            if (!empty($this->toolConfigs['enabled'])) {
                return true;
            }

            return false;
        }

        /**
         * Checks if this tool supports anonymous usage where
         * settings are set on page
         *
         * @return boolean true for supported, false for unsupported
         */
        public function supportsAnonymousUse()
        {
            return $this->anonymousSupport;
        }

        /**
         * Checks if this tool is placed inline or floating. Inline
         * tools get widgets and short codes. Floating tools do not.
         *
         * @return boolean true for inline, false floating
         */
        public function inlineTool()
        {
            return $this->inline;
        }

        /**
         * Checks if this tool is only for pro users. This is used to make sure
         * we only display tools to users that will work for them.
         *
         * @return boolean true for pro, false for basic
         */
        public function isProTool()
        {
            if ($this->edition == 'pro') {
                return true;
            }

            return false;
        }

        /**
         * Checks if this plugin is in anonymous mode where settings are set
         * on page, and not lojson/boost mode where settings are retrieved from
         * AddThis.com
         *
         * @return boolean false for lojson/boost mode, true for layers API /
         * anonymous mode
         */
        public function inAnonymousMode()
        {
            $this->getGlobalOptionsObject();
            return $this->globalOptionsObject->inAnonymousMode();
        }

        /**
         * Checks if this profile on the account is pro or basic
         *
         * @return boolean false for basic, true for pro
         */
        public function isProProfile()
        {
            $this->getGlobalOptionsObject();
            return $this->globalOptionsObject->isProProfile();
        }

        /**
         * Checks if this plugin is in lojson/boost mode where settings are
         * retrieved from AddThis.com and not set on page
         *
         * @return boolean true for lojson/boost mode, false for layers API mode
         */
        public function inRegisteredMode()
        {
            $this->getGlobalOptionsObject();
            return $this->globalOptionsObject->inRegisteredMode();
        }

        /**
         * This must be public as it's used in the tool's widget for inline
         * tools
         *
         * Returns HTML for creating this tool on page.
         *
         * @param array $args     settings for this widget (we only use
         * widget_id)
         * @param array $instance settings for this particular tool, if not
         * being used from the tool settings (a widget instance)
         *
         * @return string this should be valid html
         */
        public function getInlineCode($args = array(), $instance = array())
        {
            if (!$this->inlineTool()) {
                $html = '<!-- this tool cannot be added inline -->' . "\n";
            } elseif ($this->inAnonymousMode() && !$this->isEnabled()) {
                $html = '<!-- ' . $this->prettyName . ' is not enabled -->';
            } elseif (!is_string($this->layersClass)) {
                $html = '<!-- layers class not set for this tool -->';
            } else {
                $html = '<div class="'.$this->layersClass.'"></div>';

                $featureConfigs = $this->getFeatureConfigs();
                if (isset($featureConfigs[$this->settingsSubVariableName])) {
                    $toolConfigs = $featureConfigs[$this->settingsSubVariableName];
                }

                if (!empty($toolConfigs)
                    && !empty($toolConfigs['conflict'])
                ) {
                    // do special stuff if widget is in conflict mode
                    $class = $args['widget_id'];
                    $layers = $this->getAddThisLayers($instance);
                    $layers[$this->layersApiProductName]['elements'] = '.'.$class;
                    $toolHtml = '<div class="'.$class.'"></div>';

                    $layersJson = json_encode((object)$layers);

                    $addLayersJavaScript = '<script>';
                    $addLayersJavaScript .= '  if (typeof window.addthis_layers_tools === \'undefined\') { ';
                    $addLayersJavaScript .= '    window.addthis_layers_tools = ['.$layersJson.']';
                    $addLayersJavaScript .= '  } else { ';
                    $addLayersJavaScript .= '    window.addthis_layers_tools.push('.$layersJson.');';
                    $addLayersJavaScript .= '  }';
                    $addLayersJavaScript .= '</script>';
                    $html = $addLayersJavaScript . $toolHtml;
                } else {
                    $html = '<div class="'.$this->layersClass.'"></div>';
                }

                $gooSettings = $this->getGlobalOptionsConfigs();
                if (!empty($gooSettings['ajax_support'])) {
                    $html .= '<script>if (typeof window.atnt !== \'undefined\') { window.atnt(); }</script>';
                }
            }

            return $html;
        }

        /**
         * This must be public as it's used in a callback for add_shortcode
         *
         * Returns HTML to use to replace a short tag for this tool. Includes
         * tags to identify its from a short code.
         *
         * @return string this should be valid html
         */
        public function getInlineCodeForShortCode()
        {
            $html  = '<!-- Created with a shortcode from an AddThis plugin -->';
            $html .= '<!-- tool name: ' . $this->prettyName . ' -->';
            $html .= $this->getInlineCode();
            $html .= '<!-- End of short code snippet -->';

            return $html;
        }

        /**
         * The openning tag for short tags for this tool.
         *
         * @return string
         */
        public function getShortCodeOpen()
        {
            $code = '[' . $this->shortCode . ']';
            return $code;
        }

        /**
         * The closing tags for short tags for this tool. If this tools short
         * tags don't have a closing tag, then just return an empty string.
         *
         * @return string
         */
        public function getShortCodeClose()
        {
            $code = '';
            return $code;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_share
         *
         * @return array an associative array
         */
        public function getAddThisShare()
        {
            $toolShare = array();
            return $toolShare;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_config
         *
         * @return array an associative array
         */
        public function getAddThisConfig()
        {
            $toolConfig = array();
            return $toolConfig;
        }

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @return array an associative array
         */
        public function getAddThisLayers()
        {
            $toolLayers = array();
            return $toolLayers;
        }

        /**
         * Returns a string describing the type of template we're currently on
         *
         * @return string|null home, archives, categories, pages, post or false
         * on unknown
         */
        public static function currentTemplateType()
        {
            global $post;

            // determine page type
            if (is_home() || is_front_page()) {
                $type = 'home';
            } elseif (is_archive()) {
                $type = 'archives';
                if (is_category()) {
                    $type = 'categories';
                }
            } elseif (is_object($post)
                && ($post instanceof WP_Post)
                && !empty($post->ID)
                && is_page($post->ID)
            ) {
                $type = 'pages';
            } elseif (is_single()) {
                $type = 'posts';
            } else {
                $type = false;
            }

            return $type;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes form input for a  tool sub settings variable, manipulates
         * it, and returns the variables that should be saved to the database.
         * All tools should override thie function, as all it does here is
         * sanitize anything given to it.
         *
         * @param array   $input             An associative array of values
         * input for this tools' settings
         * @param boolean $addDefaultConfigs Whether to populate in default
         * values for missing fields
         *
         * @return array A cleaned up associative array of settings specific to
         *               this tool.
         */
        public function sanitizeSettings($input, $addDefaultConfigs = true)
        {
            $output = array();

            if (is_array($input)) {
                foreach ($input as $field => $value) {
                    if (!empty($value)) {
                        $output[$field] = sanitize_text_field($value);
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes configs and adds default values where not present
         *
         * @param array $configs An associative array of values input for this
         * tools' settings
         *
         * @return array An associative array of settings specific to this tool
         *               with added defaults where not already present.
         */
        public function addDefaultConfigs($configs)
        {
            if (is_array($configs)) {
                foreach ($this->defaultConfigs as $field => $defaultValue) {
                    if (!isset($configs[$field])) {
                        $configs[$field] = $defaultValue;
                        $this->addedDefaultValue = true;
                    }
                }
            } else {
                $configs = $this->defaultConfigs;
                $this->addedDefaultValue = true;
            }

            return $configs;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * @return boolean true is available to user, false if not
         */
        public function isAvailable()
        {
            $this->getGlobalOptionsObject();

            if (($this->inAnonymousMode() && !$this->supportsAnonymousUse())
                || ($this->isProTool() && !$this->isProProfile())
            ) {
                return false;
            }

            return true;
        }

        /**
         * This must be public as it's used in widgets
         *
         * @param string $buttonName The text of the button that the user presses
         * to indicate that they agree without EULA
         *
         * @return string End User License Agreement text
         */
        public function eulaText($buttonName = 'Save')
        {
            $buttonName = esc_html__($buttonName, AddThisFeature::$l10n_domain);

            $eulaTemplate = 'By clicking "%1$s" you certify that you are at least 13 years old, and agree to the AddThis %2$s and %3$s.';
            $eulaTemplate = esc_html__($eulaTemplate, AddThisFeature::$l10n_domain);

            $privacyPolicyText = esc_html__(
                'Privacy Policy',
                AddThisFeature::$l10n_domain
            );

            $termsOfServiceText = esc_html__(
                'Terms of Service',
                AddThisFeature::$l10n_domain
            );

            $privacyPolicyLink = '<a href="http://www.addthis.com/privacy/privacy-policy">'.$privacyPolicyText.'</a>';
            $termsOfServiceLink = '<a href="http://www.addthis.com/tos">'.$termsOfServiceText.'</a>';

            $eula = sprintf($eulaTemplate, $buttonName, $privacyPolicyLink, $termsOfServiceLink);
            return $eula;
        }
    }
}