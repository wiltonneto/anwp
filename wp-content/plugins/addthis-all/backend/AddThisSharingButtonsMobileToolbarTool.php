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
require_once 'AddThisFollowButtonsToolParent.php';

if (!class_exists('AddThisSharingButtonsMobileToolbarTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Mobile Toolbar tools
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsMobileToolbarTool extends AddThisSharingButtonsToolParent
    {
        public $prettyName = 'Mobile Toolbar';

        public $edition = 'basic';
        public $anonymousSupport = true;
        public $inline = false;
        public $settingsSubVariableName = 'smlmo';
        public $layersApiProductName = 'dock';

        protected $defaultConfigs = array(
            'enabled'           => false,
            'follow'            => 'on', // off
            'buttonBarTheme'    => 'light', // gray or dark
            'buttonBarPosition' => 'bottom', // top
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
                'follow'             => $configs['follow'],
                'buttonBarTheme'     => $configs['buttonBarTheme'],
                'buttonBarPosition'  => $configs['buttonBarPosition'],
            );

            $layers['followServices'] = AddThisFollowButtonsToolParent::formatServicesForAddThisLayers($configs['followServices']);

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
                        case 'follow':
                            if ($value === 'off' || $value === 'on') {
                                $output[$field] = $value;
                            }
                            break;
                        case 'followServices':
                            if (is_array($value)) {
                                foreach ($value as $service => $username) {
                                    if (!empty($username)) {
                                        $output['followServices'][$service] = sanitize_text_field($username);
                                    }
                                }
                            }
                            break;
                        case 'buttonBarTheme':
                            if ($value === 'gray' || $value === 'dark' || $value === 'light') {
                                $output[$field] = $value;
                            }
                            break;
                        case 'buttonBarPosition':
                            if ($value === 'top' || $value === 'bottom') {
                                $output[$field] = $value;
                            }
                            break;
                    }
                }
            }

            if ($addDefaultConfigs) {
                $output = $this->addDefaultConfigs($output);
            }

            if (empty($output['followServices'])) {
                $output['followServices'] = new stdClass();
            }

            return $output;
        }
    }
}