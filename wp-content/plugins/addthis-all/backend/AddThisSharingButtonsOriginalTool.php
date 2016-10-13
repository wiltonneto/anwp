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
require_once 'AddThisSharingButtonsToolParent.php';

if (!class_exists('AddThisSharingButtonsOriginalTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Original Sharing Button tools
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsOriginalTool extends AddThisSharingButtonsToolParent
    {
        public $layersClass = 'addthis_native_toolbox';
        public $prettyName = 'Original Sharing Buttons';

        public $edition = 'basic';
        public $anonymousSupport = true;
        public $inline = true;
        public $settingsSubVariableName = 'scopl';
        public $layersApiProductName = 'sharetoolbox';

        public $widgetClassName = 'AddThisSharingButtonsOriginalWidget';
        public $widgetBaseId = 'addthis_native_toolbox_widget';
        public $widgetName = 'Sharing Buttons - Original';
        public $widgetDescription = 'Original sharing buttons from AddThis to increase your visitors\' social shares.';
        public $defaultWidgetTitle = 'Share';

        public $shortCode = 'addthis_original_sharing_buttons';

        protected $defaultConfigs = array(
            'enabled'               => false,
            'services'              => array(
                'facebook_like',
                'tweet',
                'pinterest_pinit',
                'google_plusone',
                'counter',
            ),
            'elements'              => array(
                '.addthis_native_toolbox',
                '.at-below-post-homepage',
                '.at-above-post',
                '.at-below-post',
                '.at-above-post-page',
                '.at-below-post-page',
                '.at-above-post-cat-page',
                '.at-below-post-cat-page',
                '.at-above-post-arch-page',
                '.at-below-post-arch-page',
                '.at-above-post-homepage',
            ),
        );

        /**
         * Creates tool specific settings for the JavaScript variable
         * addthis_layers, used to bootstrap layers
         *
         * @param array $configs optional array of settings (used with widgets)
         *
         * @return array an associative array
         */
        public function getAddThisLayers($configs = array())
        {
            if (empty($configs)) {
                $configs = $this->getToolConfigs();
            }

            $layers = array(
                'numPreferredServices' => 5,
                'thirdPartyButtons'    => true,
            );

            if (!empty($configs['services']) && is_array($configs['services'])) {
                $layers['services'] = implode(',', $configs['services']);
            }

            if (!empty($configs['elements']) && is_array($configs['elements'])) {
                $layers['elements'] = implode(',', $configs['elements']);
            }

            $result = array($this->layersApiProductName => $layers);
            return $result;
        }

        /**
         * This must be public as it's used in the feature object with this tool
         *
         * This takes form input for a  tool sub settings variable, manipulates
         * it, and returns the variables that should be saved to the database.
         *
         * @param array   $input             An associative array of values
         * input for this tools' settings
         * @param boolean $addDefaultConfigs Whether to populate in default
         * values for missing fields
         *
         * @return array A cleaned up associative array of settings specific to
         *               this feature.
         */
        public function sanitizeSettings($input, $addDefaultConfigs = true)
        {
            $output = array();

            if (is_array($input)) {
                foreach ($input as $field => $value) {
                    switch ($field) {
                        case 'enabled':
                            $output[$field] = (boolean)$value;
                            break;
                        case 'services':
                        case 'elements':
                            if (is_array($value) && !empty($value)) {
                                $output[$field] = array();
                                foreach ($value as $service) {
                                    $output[$field][] = sanitize_text_field($service);
                                }
                            }
                            break;
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            return $output;
        }
    }
}