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
require_once 'AddThisWidget.php';

if (!class_exists('AddThisGlobalOptionsWidget')) {
    /**
     * WordPress widget class for AddThis Horizontal Follow Buttons
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools\Widgets
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisGlobalOptionsWidget extends AddThisWidget
    {
        /**
         * Bootstraps the widget for WordPress. It determines its tool settings
         * class name, and passes that string to its parent constructor.
         *
         * @return null
         */
        public function __construct()
        {
            $toolClassName = self::getToolClass(__CLASS__);
            parent::__construct($toolClassName);
        }

        /**
         * Prints out HTML for the options form in the WordPress admin Dashboard
         *
         * @param array $instance The widget options
         *
         * @return null
         */
        public function form($instance)
        {
            $featureObject = $this->toolClass->getFeatureObject();
            $url = $featureObject->getSettingsPageUrl();

            $settingsText = esc_html__('the plugin\'s settings', AddThisFeature::$l10n_domain);
            $settingsLink = '<a href="'.$url.'">'.$settingsText.'</a>';

            $links = array();
            $links[] = $settingsLink;

            if ($this->toolClass->inRegisteredMode()) {
                $profileId = $featureObject->globalOptionsObject->getProfileId();
                $dashboardUrl = 'https://www.addthis.com/dashboard#gallery/pub/'.$profileId;
                $links[] = '<a href="'.$dashboardUrl.'" target="_blank">addthis.com</a>';
            }

            $editLink = '';
            if (count($links) == 1) {
                $editLinkTemplate = 'To edit the options for your AddThis tools, please go to %1$s';
                $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                $editLink = sprintf($editLinkTemplate, $links[0]);
            } elseif (count($links) > 1) {
                $editLinkTemplate = 'To edit the options for your AddThis tools, please go to %1$s or %2$s';
                $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                $editLink = sprintf($editLinkTemplate, $links[0], $links[1]);
            }

            $html = '<p>'.$this->toolClass->widgetDescription.'</p>';
            $html .= '<p>'.$editLink.'</p>';
            $html .= '<p>'.$this->toolClass->eulaText('Save').'</p>';

            echo $html;
        }
    }
}