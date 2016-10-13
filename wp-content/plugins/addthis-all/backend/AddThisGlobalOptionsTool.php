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
require_once 'AddThisTool.php';

if (!class_exists('AddThisGlobalOptionsTool')) {
    /**
     * AddThis' tool class for putting JavaScript on page
     *
     * @category   GlobalOptions
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisGlobalOptionsTool extends AddThisTool
    {
        protected $featureClassName = 'AddThisGlobalOptionsFeature';

        public $prettyName = 'AddThis Script';

        public $edition = 'basic';
        public $anonymousSupport = true;
        public $inline = true;

        public $widgetClassName = 'AddThisGlobalOptionsWidget';
        public $widgetBaseId = 'addthis_global_options_widget';
        public $widgetName = 'AddThis Script';
        public $widgetDescription = 'If your theme is not adding the AddThis script (addthis_widget.js) onto your pages, try adding this widget.';

        public $shortCode = 'addthis_script';

        /**
         * This must be public as it's used in the tool's widget
         *
         * Returns HTML for adding the script onto a page
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
            if (is_feed()) {
                return '';
            }

            $configs = $this->getFeatureConfigs();
            $addthisWidgetUrl = $this->featureObject->getAddThisWidgetJavaScriptUrl();

            if (!empty($configs['addthis_asynchronous_loading'])) {
                $async = ' async="async"';
            } else {
                $async = '';
            }

            $script = '
                <script
                    data-cfasync="false"
                    type="text/javascript"
                    src="'.admin_url('admin-ajax.php') . '?action='.$this->featureObject->publicJavaScriptAction.'"'
                    . $async . '
                >
                </script>
                <script
                    data-cfasync="false"
                    type="text/javascript"
                    src="' . $addthisWidgetUrl . '"'
                    . $async . '
                ></script>
            ';

            return $script;
        }
    }
}