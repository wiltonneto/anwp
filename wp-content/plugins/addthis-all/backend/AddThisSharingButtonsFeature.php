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
require_once 'AddThisSharingButtonsSquareWidget.php';
require_once 'AddThisSharingButtonsSquareTool.php';
require_once 'AddThisSharingButtonsOriginalWidget.php';
require_once 'AddThisSharingButtonsOriginalTool.php';
require_once 'AddThisSharingButtonsCustomWidget.php';
require_once 'AddThisSharingButtonsCustomTool.php';
require_once 'AddThisSharingButtonsJumboWidget.php';
require_once 'AddThisSharingButtonsJumboTool.php';
require_once 'AddThisSharingButtonsResponsiveWidget.php';
require_once 'AddThisSharingButtonsResponsiveTool.php';
require_once 'AddThisSharingButtonsSidebarTool.php';
require_once 'AddThisSharingButtonsMobileToolbarTool.php';
require_once 'AddThisSharingButtonsMobileSharingToolbarTool.php';
require_once 'AddThisFollowButtonsFeature.php';

if (!class_exists('AddThisSharingButtonsFeature')) {
    /**
     * Class for adding AddThis sharing buttonst tools to WordPress
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Features
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsFeature extends AddThisFeature
    {
        protected $settingsVariableName = 'addthis_sharing_buttons_settings';
        protected $settingsPageId = 'addthis_sharing_buttons';
        protected $name = 'Share Buttons';
        protected $SharingButtonsSquareToolObject = null;
        protected $SharingButtonsOriginalToolObject = null;
        protected $SharingButtonsCustomToolObject = null;
        protected $SharingButtonsJumboToolObject = null;
        protected $SharingButtonsResponsiveToolObject = null;
        protected $SharingButtonsSidebarToolObject = null;
        protected $SharingButtonsMobileToolbarToolObject = null;
        protected $SharingButtonsMobileSharingToolbarToolObject = null;
        protected $filterPriority = 1;
        protected $filterNamePrefix = 'addthis_sharing_buttons_';
        protected $enableAboveContent = true;
        protected $enableBelowContent = true;

        // a list of all settings fields used for this feature that aren't tool
        // specific
        protected $settingsFields = array(
            'quick_tag',
            'startUpgradeAt',
        );

        public $globalLayersJsonField = 'addthis_layers_share_json';
        public $globalEnabledField = 'sharing_buttons_feature_enabled';

        // require the files with the tool and widget classes at the top of this
        // file for each tool
        protected $tools = array(
            'SharingButtonsSquare',
            'SharingButtonsOriginal',
            'SharingButtonsCustom',
            'SharingButtonsJumbo',
            'SharingButtonsResponsive',
            'SharingButtonsSidebar',
            'SharingButtonsMobileToolbar',
            'SharingButtonsMobileSharingToolbar',
        );

        protected $quickTagId = 'addthis_share';
        /**
         * Review https://codex.wordpress.org/Quicktags_API for access keys used
         * by WordPress
         */
        protected $quickTagAccessKey = 'y';

        public $contentFiltersEnabled = true;

        /**
         * Builds the class used for sharing buttons above and below content on
         * pages, posts, categories, archives and the homepage
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
            $pageTypeClean = AddThisTool::currentTemplateType();
            switch ($pageTypeClean) {
                case 'home':
                    $appendClass = 'post-homepage';
                    break;
                case 'archives':
                    $appendClass = 'post-arch-page';
                    break;
                case 'categories':
                    $appendClass = 'post-cat-page';
                    break;
                case 'pages':
                    $appendClass = 'post-page';
                    break;
                case 'posts':
                    $appendClass = 'post';
                    break;
                default:
                    $appendClass = false;
            }

            if ($location == 'above') {
                $toolClass = 'at-above-' . $appendClass;
                $filterName = $this->filterNamePrefix . 'above_tool';
            } else {
                $toolClass = 'at-below-' . $appendClass;
                $filterName = $this->filterNamePrefix . 'below_tool';
            }

            if (!$appendClass) {
                $toolClass = false;
            }

            $toolClass = $this->applyToolClassFilters($toolClass, $location, $track);
            return $toolClass;
        }

        /**
         * Figures out the URL to use when sharing a post or page and returns it.
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a URL
         */
        public function getShareUrl(&$track = false)
        {
            $url = get_permalink();
            /**
             * This filter allows users to hook into the plugin and change the
             * url used on an item. A flasey value will not add the data-url
             * attribute
             */
            $url = $this->applyFilter($this->filterNamePrefix . 'url', $url, $track);
            return $url;
        }

        /**
         * Figures out the title to use when sharing a post or page and returns
         * it.
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string a title
         */
        public function getShareTitle(&$track = false)
        {
            $title = the_title_attribute('echo=0');
            /**
             * This filter allows users to hook into the plugin and change the
             * title used on an item. A flasey value will not add the data-title
             * attribute
             */
            $title = $this->applyFilter($this->filterNamePrefix . 'title', $title, $track);
            $title = htmlspecialchars($title);
            return $title;
        }

        /**
         * Builds HTML for teling AddThis what URL to share for inline layers
         * buttons
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string HTML attributes for telling AddThis what URL to share
         */
        public function getInlineLayersAttributes(&$track = false)
        {
            $dataUrlTemplate = 'data-url="%1$s"';
            $dataTitleTemplate = 'data-title="%1$s"';

            $attrs = array();
            $url = $this->getShareUrl($track);
            if (!empty($url)) {
                $attrs[] = sprintf($dataUrlTemplate, $url);
            }

            $title = $this->getShareTitle($track);
            if (!empty($title)) {
                $attrs[] = sprintf($dataTitleTemplate, $title);
            }

            $attrString = implode(' ', $attrs);
            return $attrString;
        }

        /**
         * Builds HTML for teling AddThis what URL to share for inline buttons
         * rendered using the old client API
         *
         * @param array $track Optional. Used by reference. If the
         * filter changes the value in any way the filter's name will be pushed
         *
         * @return string HTML attributes for telling AddThis what URL to share
         */
        public function getInlineClientApiAttributes(&$track = false)
        {
            $dataUrlTemplate = 'addthis:url="%1$s"';
            $dataTitleTemplate = 'addthis:title="%1$s"';

            $url = $this->getShareUrl($track);
            if (!empty($url)) {
                $attrs[] = sprintf($dataUrlTemplate, $url);
            }

            $title = $this->getShareTitle($track);
            if (!empty($title)) {
                $attrs[] = sprintf($dataTitleTemplate, $title);
            }

            $attrString = implode(' ', $attrs);
            return $attrString;
        }

        /**
         * Upgrade from Smart Layers by AddThis 1.*.* to
         * Smart Layers by AddThis 2.0.0
         *
         * @return null
         */
        protected function upgradeIterative1()
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

            $followServices = array();
            if (!empty($jsonDecoded['follow']) &&
                !empty($jsonDecoded['follow']['services'])
            ) {
                // prep mobile toolbar folllow settings
                $oldServices = $jsonDecoded['follow']['services'];
                $followServices = AddThisFollowButtonsFeature::upgradeIterative2SmartLayersServices($oldServices);
            }

            $sharingSidebarConfigs = array();
            $mobileToolbarConfigs = array();
            if (isset($jsonDecoded['share'])) {
                // prep sharing sidebar settings & mobile toolbar settings
                $sharingSidebarConfigs['enabled'] = true;
                $mobileToolbarConfigs['enabled'] = true;

                if (isset($jsonDecoded['share']['position'])) {
                    $sharingSidebarConfigs['position'] = $jsonDecoded['share']['position'];
                }

                if (isset($jsonDecoded['share']['numPreferredServices'])) {
                    $sharingSidebarConfigs['numPreferredServices'] = (int)$jsonDecoded['share']['numPreferredServices'];
                }

                if (!empty($followServices)) {
                    // include follow services for mobile
                    $mobileToolbarConfigs['follow'] = 'on';
                    $mobileToolbarConfigs['followServices'] = $followServices;
                } else {
                    $mobileToolbarConfigs['follow'] = 'off';
                }

                if (isset($jsonDecoded['theme'])) {
                    $sharingSidebarConfigs['theme'] = $jsonDecoded['theme'];
                    $mobileToolbarConfigs['buttonBarTheme'] = $jsonDecoded['theme'];
                }
            }

            $this->configs['smlsh'] = $sharingSidebarConfigs;
            $this->configs['smlmo'] = $mobileToolbarConfigs;
        }
    }
}