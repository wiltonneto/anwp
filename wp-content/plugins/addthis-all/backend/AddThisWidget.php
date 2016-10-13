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

if (!class_exists('AddThisWidget')) {
    /**
     * AddThis' root parent class for all its widgets
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Tools\Widgets
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisWidget extends WP_Widget
    {
        public $toolClassName = null;
        public $toolClass = null;

        /**
         * Registers widget with WordPress.
         *
         * @param string $toolClassName the class name for this widget that know
         * how to create this tool's functionality
         *
         * @return null
         */
        public function __construct($toolClassName)
        {
            if (!class_exists($toolClassName)) {
                error_log(__METHOD__ . ' class ' . $toolClassName . ' does not exists.');
                return null;
            }

            $this->toolClassName = $toolClassName;
            $toolClass = new $toolClassName();
            $this->toolClass = $toolClass;

            $name = __($toolClass->widgetName, AddThisFeature::$l10n_domain);
            $description = __($toolClass->widgetDescription, AddThisFeature::$l10n_domain);

            $widgetOptions = array(
                'description' => $description,
            );

            $controlOptions = array();

            parent::__construct(
                $toolClass->widgetBaseId,
                $name,
                $widgetOptions,
                $controlOptions
            );
        }

        /**
         * Prints out HTML for the content of the widget
         *
         * @param array $args     Widget arguments
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return null
         */
        public function widget($args, $instance)
        {
            $titleHtml = '';
            if (isset($args['before_title'])) {
                $titleHtml = $titleHtml . $args['before_title'];
            }
            if (!empty($instance['title'])) {
                $titleHtml = $titleHtml . $instance['title'];
            }
            if (isset($args['after_title'])) {
                $titleHtml = $titleHtml . $args['after_title'];
            }

            $addThisToolCode = $this->toolClass->getInlineCode($args, $instance);
            if (!isset($args['widget_name'])) {
                $args['widget_name'] = 'no name';
            }
            if (!isset($args['before_widget'])) {
                $args['before_widget'] = '';
            }
            if (!isset($args['after_widget'])) {
                $args['after_widget'] = '';
            }

            $html = '
                '.$args['before_widget'].'
                <!-- Widget added by an AddThis plugin -->
                    <!-- widget name: ' . $args['widget_name'] . ' -->
                    <!-- tool class: ' . $this->toolClassName . ' -->
                    '.$titleHtml.'
                    '.$addThisToolCode.'
                <!-- End of widget -->
                '.$args['after_widget'].'
            ';

            echo $html;
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
            $titleFieldId = $this->get_field_id('title');
            $titleFieldName = $this->get_field_name('title');
            $titleLabel = esc_html__('Title: ', AddThisFeature::$l10n_domain);

            $featureObject = $this->toolClass->getFeatureObject();
            $toolConfigs = $this->toolClass->getToolConfigs();
            $url = $featureObject->getSettingsPageUrl().'#/follow/pco/'.$this->toolClass->settingsSubVariableName;

            if (isset($instance['title'])) {
                $titleValue = esc_attr($instance['title']);
            } else {
                $titleDefault = $this->toolsClass->defaultWidgetTitle;
                $titleValue = esc_html__($titleDefault, AddThisFeature::$l10n_domain);
            }

            $settingsText = esc_html__('the plugin\'s settings', AddThisFeature::$l10n_domain);
            $settingsLink = '<a href="'.$url.'">'.$settingsText.'</a>';

            $conflictString = '';
            if (empty($toolConfigs['conflict'])) {
                $links = array();

                $featureObject = $this->toolClass->getFeatureObject();
                if ($featureObject->isEnabled()) {
                    $links[] = $settingsLink;
                }

                if (!$featureObject->isEnabled() || $this->toolClass->inRegisteredMode()) {
                    $profileId = $featureObject->globalOptionsObject->getProfileId();
                    $dashboardUrl = 'https://www.addthis.com/dashboard#gallery/pub/'.$profileId
                        .'/pco/'.$this->toolClass->settingsSubVariableName;
                    $links[] = '<a href="'.$dashboardUrl.'" target="_blank">addthis.com</a>';
                }

                $editLink = '';
                if (count($links) == 1) {
                    $editLinkTemplate = 'To edit the options for this tool, please go to %1$s';
                    $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                    $editLink = sprintf($editLinkTemplate, $links[0]);
                } elseif (count($links) > 1) {
                    $editLinkTemplate = 'To edit the options for this tool, please go to %1$s or %2$s';
                    $editLinkTemplate = esc_html__($editLinkTemplate, AddThisFeature::$l10n_domain);
                    $editLink = sprintf($editLinkTemplate, $links[0], $links[1]);
                }

                $html = '
                    <p>
                        <label
                            for="'.$titleFieldId.'"
                        >
                            '.$titleLabel.'
                        </label>
                        <input
                            class="widefat"
                            id="'.$titleFieldId.'"
                            name="'.$titleFieldName.'"
                            type="text"
                            value="'.$titleValue.'"
                        />
                    </p>
                    <p>
                        '.$editLink.'
                    </p>
                ';
            } else {
                $conflictTemplate = 'CONFLICT! Some of the configuration options you chose for this plugin are no longer supported and can not be upgraded automatically. Please go to %1$s to update your configuration before adding or editing this widget.';
                $conflictTemplate = esc_html__($conflictTemplate, AddThisFeature::$l10n_domain);

                $conflictHtml = sprintf($conflictTemplate, $settingsLink);

                $html = '
                <p>
                    <strong>
                        '.$conflictHtml.'
                    </strong>
                    <br />
                </p>
                ';
            }

            $html .= '<p>'.$this->toolClass->eulaText('Save').'</p>';

            echo $html;
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance options values just sent to be saved
         * @param array $old_instance previously options values (from database)
         *
         * @return array
         */
        public function update($new_instance, $old_instance)
        {
            $instance = $old_instance;

            if (isset($new_instance['title'])) {
                $instance['title'] = strip_tags($new_instance['title']);
            } else {
                $titleDefault = $this->toolsClass->defaultWidgetTitle;
                $titleValue = esc_html__($titleDefault, AddThisFeature::$l10n_domain);
                $instance['title'] = $titleDefault;
            }

            return $instance;
        }

        /**
         * Creates the class name for this widget that know how to create this
         * tool's functionality
         *
         * @param string $myClassName the name of the Widgets class
         *
         * @return string the name of the tool's setting class
         */
        public static function getToolClass($myClassName)
        {
            $length = strlen($myClassName);
            $cutoff = strlen('Widget');
            $toolName = substr($myClassName, 0, $length - $cutoff);
            $toolClassName = $toolName . 'Tool';

            return $toolClassName;
        }
    }
}