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

require_once 'AddThisFeature.php';
require_once 'AddThisFollowButtonsHorizontalWidget.php';
require_once 'AddThisFollowButtonsHorizontalTool.php';
require_once 'AddThisFollowButtonsVerticalWidget.php';
require_once 'AddThisFollowButtonsVerticalTool.php';
require_once 'AddThisFollowButtonsCustomWidget.php';
require_once 'AddThisFollowButtonsCustomTool.php';
require_once 'AddThisFollowButtonsHeaderTool.php';

if (!class_exists('AddThisFollowButtonsFeature')) {
    /**
     * Class for adding AddThis follow buttons tools to WordPress
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsFeature extends AddThisFeature
    {
        //addthis_follow_settings,widget_addthis-follow-widget,addthis_settings
        protected $oldConfigVariableName = 'addthis_follow_settings';
        protected $settingsVariableName = 'addthis_follow_buttons_settings';
        protected $settingsPageId = 'addthis_follow_buttons';
        protected $name = 'Follow Buttons';
        protected $FollowButtonsHorizontalToolObject = null;
        protected $FollowButtonsVerticalToolObject = null;
        protected $FollowButtonsCustomToolObject = null;
        protected $FollowButtonsHeaderToolObject = null;
        protected $filterNamePrefix = 'addthis_follow_buttons_';

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array(
            'quick_tag',
            'startUpgradeAt',
        );

        protected $defaultConfigs = array(
            'quick_tag' => 'disabled',
        );

        // used for temporary compatability with the Sharing Buttons plugin
        public $globalLayersJsonField = 'addthis_layers_follow_json';
        public $globalEnabledField = 'follow_buttons_feature_enabled';

        // require the files with the tool and widget classes at the top of this
        // file for each tool
        // make a protected variable matching the name for each
        // tool + ToolObject
        protected $tools = array(
            'FollowButtonsHorizontal',
            'FollowButtonsVertical',
            'FollowButtonsCustom',
            'FollowButtonsHeader',
        );

        protected $quickTagId = 'addthis_follow';
        /**
         * Review https://codex.wordpress.org/Quicktags_API for access keys used
         * by WordPress
         */
        protected $quickTagAccessKey = 'x';

        /**
         * Upgrade to Follow Buttons by AddThis 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative1()
        {
            $horizontalMerge = array();
            $horizontalWidgetKeys = array();
            $verticalMerge = array();
            $verticalWidgetKeys = array();
            $widgetIdMapping = array();

            $oldWidgetName = 'addthis-follow-widget';
            $oldWidgets = get_option('widget_' . $oldWidgetName);

            if (!is_array($oldWidgets)) {
                return null;
            }

            foreach ($oldWidgets as $key => $widget) {
                if ($key == '_multiwidget') {
                    continue;
                }

                $newWidget = $this->upgradeIterative1ReformatWidgetData($widget);
                $oldWidgets[$key] = $newWidget;

                $toolName = $newWidget['tool'];

                if (isset($newWidget['title'])) {
                    unset($newWidget['title']);
                }

                if (isset($newWidget['tool'])) {
                    unset($newWidget['tool']);
                }

                if ($toolName == 'horizontal') {
                    $horizontalMerge = array_merge_recursive($horizontalMerge, $newWidget);
                    $horizontalWidgetKeys[] = $key;
                } else {
                    $verticalMerge = array_merge_recursive($verticalMerge, $newWidget);
                    $verticalWidgetKeys[] = $key;
                }
            }

            // horizontal widgets upgrade
            $newWidgetsName = 'addthis_horizontal_follow_toolbox_widget';
            $toolCode = 'flwh';

            $newWidgetIdMapping = $this->upgradeIterative1CreateNewWidgets(
                $oldWidgets,
                $horizontalWidgetKeys,
                $horizontalMerge,
                $oldWidgetName,
                $newWidgetsName,
                $toolCode
            );

            $widgetIdMapping = array_merge($widgetIdMapping, $newWidgetIdMapping);

            // vertical widgets upgrade
            $newWidgetsName = 'addthis_vertical_follow_toolbox_widget';
            $toolCode = 'flwv';
            $newWidgetIdMapping = $this->upgradeIterative1CreateNewWidgets(
                $oldWidgets,
                $verticalWidgetKeys,
                $verticalMerge,
                $oldWidgetName,
                $newWidgetsName,
                $toolCode
            );

            $widgetIdMapping = array_merge($widgetIdMapping, $newWidgetIdMapping);

            // Update sidebar pointers to use upgraded widgets
            $this->upgradeIterative1MigrateSidebarWidgetIds($widgetIdMapping);
        }

        /**
         *  Takes in data about one type of widget, and saves as new widgits
         *
         * @param array  $oldWidgets         an array with old widget settings
         * @param array  $relevantWidgetKeys an array with the keys (for the
         * array above) of widgets of the desired type
         * @param array  $mergedWidgetConfig an array of new widget settings
         * @param string $oldWidgetName      the name of the old widgets
         * @param string $newWidgetsName     the name of the new widgets
         * @param string $toolCode           the name of the tool in this plugin
         * (used to add settings to $this->config)
         *
         * @return array old widget id => new widget id
         */
        protected function upgradeIterative1CreateNewWidgets(
            $oldWidgets,
            $relevantWidgetKeys,
            $mergedWidgetConfig,
            $oldWidgetName,
            $newWidgetsName,
            $toolCode
        ) {
            $results = $this->upgradeIterative1CleanUpWidgetMerge(
                $mergedWidgetConfig
            );
            $conflict = $results['conflict'];
            $toolConfigs = $results['settings'];
            $newWidgetSettings = array();
            $widgetIdMapping = array();

            $newKey = 0;
            // simplify per widget settings because there are no conflicts
            foreach ($relevantWidgetKeys as $oldkey) {
                $newKey++;
                $widget = $this->upgradeIterative1UpgradeWidget(
                    $oldWidgets[$oldkey],
                    $conflict
                );
                $newWidgetSettings[$newKey] = $widget;

                $oldWidgetId = $oldWidgetName . '-' . $oldkey;
                $newWidgetId = $newWidgetsName . '-' . $newKey;
                $widgetIdMapping[$oldWidgetId] = $newWidgetId;
            }

            $this->configs[$toolCode] = $toolConfigs;
            if ($conflict['size'] || $conflict['services']) {
                $this->configs[$toolCode]['conflict'] = true;
            }

            // save new widget info
            if (!empty($newWidgetSettings)) {
                $newWidgetSettings['_multiwidget'] = 1;
                update_option('widget_' . $newWidgetsName, $newWidgetSettings);
            }
            return $widgetIdMapping;
        }

        /**
         * Takes a mapping of old widget identifiers and new widgit identifiers,
         * and updates the widget area settings for them.
         *
         * @param array $widgetIdMapping old widget id => new widget id
         *
         * @return null
         */
        protected function upgradeIterative1MigrateSidebarWidgetIds(
            $widgetIdMapping
        ) {
            $sideBarConfigs = get_option('sidebars_widgets');
            foreach ($sideBarConfigs as $sideBarId => $sideBarInfo) {
                if (!is_array($sideBarInfo)) {
                    continue;
                }

                foreach ($sideBarInfo as $key => $oldWidgetId) {
                    if (isset($widgetIdMapping[$oldWidgetId])) {
                        $newWidgetId = $widgetIdMapping[$oldWidgetId];
                        $sideBarConfigs[$sideBarId][$key] = $newWidgetId;
                    }
                }
            }

            update_option('sidebars_widgets', $sideBarConfigs);
        }

        /**
         * Prepares the reformatted widget data from
         * upgradeIterative1ReformatWidgetData to be saved, based on what
         * conflicts were found during upgrade.
         *
         * @param array $sudoWidget the old widget settings
         * @param array $conflict   and array with keys size and services,
         * telling us whether there were conflicts for those feilds
         *
         * @return array the new widget settings to be saved
         */
        protected function upgradeIterative1UpgradeWidget(
            $sudoWidget,
            $conflict
        ) {
            $widgetFields = array('title');
            foreach ($conflict as $field => $boolean) {
                if ($boolean) {
                    $widgetFields[] = $field;
                }
            }

            foreach ($sudoWidget as $field => $value) {
                if (in_array($field, $widgetFields)) {
                    $newWidget[$field] = $value;
                }
            }

            return $newWidget;
        }

        /**
         * Goes through merged data for all widgets of a type, and cleans them
         * up. Mostly dedupes arrays and turns them into a string when they only
         * have one element. This is part of determining the new settings for
         * this feature.
         *
         * @param array $mergedWidgets data from all the widgets, merged
         *
         * @return array An associative array that includes the new settings, as
         * well as information on whether there were conflicts in size or
         * service choices
         */
        protected function upgradeIterative1CleanUpWidgetMerge($mergedWidgets)
        {
            $sizeConflict = false;
            $servicesConflict = false;

            // check for conflicts & clean up
            foreach ($mergedWidgets as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }

                if ($key != 'services') {
                    $value = array_unique($value);

                    if (count($value) == 1) {
                        $value = array_pop($value);
                    } else {
                        $sizeConflict = true;
                    }

                    if (empty($value)) {
                        unset($mergedWidgets[$key]);
                    } else {
                        $mergedWidgets[$key] = $value;
                    }
                } else {
                    foreach ($value as $service => $usernames) {
                        if (!is_array($usernames)) {
                            continue;
                        }

                        $usernames = array_unique($usernames);
                        if (count($usernames) == 1) {
                            $usernames = array_pop($usernames);
                        } else {
                            $servicesConflict = true;
                        }
                        $mergedWidgets[$key][$service] = $usernames;

                        if (empty($usernames)) {
                            unset($mergedWidgets[$key][$service]);
                        } else {
                            $mergedWidgets[$key][$service] = $usernames;
                        }
                    }
                }
            }

            $results = array(
                'conflict' => array(
                    'size' => $sizeConflict,
                    'services' => $servicesConflict
                ),
                'settings' => $mergedWidgets,
            );

            return $results;
        }

        /**
         * Reformats the settings for old widgets to be closer in line with what
         * the 2.0 version of the plugin uses.
         *
         * @param array $input the old settings for the widget
         *
         * @return array
         */
        public function upgradeIterative1ReformatWidgetData($input)
        {
            $output = array(
                'enabled' => true,
            );

            $sizeLarge = array('hl', 'vl');
            $toolHorizontal = array('hl', 'hs');

            foreach ($input as $key => $value) {
                if (empty($value)
                    || $value == 'YOUR-USERNAME'
                    || $value == 'YOUR-PROFILE'
                ) {
                    continue;
                }

                switch ($key) {
                    case 'twitter':
                    case 'linkedin':
                    case 'youtube':
                    case 'flickr':
                    case 'vimeo':
                    case 'pinterest':
                    case 'instagram':
                    case 'foursquare':
                    case 'tumblr':
                    case 'rss':
                        $output['services'][$key.'_user'] = $value;
                        break;
                    case 'facebook':
                        $output['services']['facebook_user'] = $value;
                        break;
                    case 'linkedin-company':
                        $output['services']['linkedin_company'] = $value;
                        break;
                    case 'youtube-channel':
                        $output['services']['youtube_channel'] = $value;
                        break;
                    case 'google':
                        $output['services']['google_follow_user'] = $value;
                        break;
                    case 'title':
                        $output['title'] = $value;
                        break;
                    case 'style':
                        // determine size here
                        if (in_array($value, $sizeLarge)) {
                            $output['size'] = 'large';
                        } else {
                            $output['size'] = 'small';
                        }

                        if (in_array($value, $toolHorizontal)) {
                            $output['tool'] = 'horizontal';
                        } else {
                            $output['tool'] = 'vertical';
                        }
                }
            }

            return $output;
        }

        /**
         * Must be static and public to be reused in recommended content feature
         *
         * Takes old follow services from Smart Layers by AddThis 1.*.* and
         * upgrades it for Smart Layers by AddThis 2.0.0
         *
         * @param array $oldFormat an associative array from the old plugin
         *
         * @return array another associative array
         */
        public static function upgradeIterative2SmartLayersServices($oldFormat)
        {
            $newFormat = array();
            foreach ($oldFormat as $service) {
                if (isset($service['usertype']) &&
                    $service['usertype'] != 'id'
                ) {
                    $key = $service['service'] . '_' . $service['usertype'];
                } else {
                    $key = $service['service'] . '_user';
                }

                $newFormat[$key] = $service['id'];
            }

            return $newFormat;
        }

        /**
         * Upgrade from Smart Layers by AddThis 1.*.* to
         * Smart Layers by AddThis 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative2()
        {
            $activated = get_option('smart_layer_activated');
            if (empty($activated)) {
                return null;
            }

            $advancedMode = get_option('smart_layer_settings_advanced');
            if (!empty($advancedMode)) {
                return null;
            }

            $jsonString = get_option('smart_layer_settings');
            $jsonString = preg_replace('/\'/', '"', $jsonString);
            $jsonDecoded = json_decode($jsonString, true);

            $followHeaderConfigs = array();
            if (!empty($jsonDecoded['follow']) &&
                !empty($jsonDecoded['follow']['services'])
            ) {
                // prep follow header settings
                $followHeaderConfigs['enabled'] = true;

                $oldServices = $jsonDecoded['follow']['services'];
                $newServices = self::upgradeIterative2SmartLayersServices(
                    $oldServices
                );
                $followHeaderConfigs['services'] = $newServices;

                if (isset($jsonDecoded['theme'])) {
                    $followHeaderConfigs['theme'] = $jsonDecoded['theme'];
                }
            }

            $this->configs['smlfw'] = $followHeaderConfigs;
        }

        /**
         * Builds the class used for horizontal follow buttons
         *
         * @param string $location Is this for a sharing button above or below
         * content/excerpts?
         * @param array  $track    Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a class
         */
        public function getClassForTypeAndLocation(
            $location = 'above',
            &$track = false
        ) {
            $toolClass = 'addthis_horizontal_follow_toolbox';
            $toolClass = $this->applyToolClassFilters(
                $toolClass,
                $location,
                $track
            );
            return $toolClass;
        }
    }
}