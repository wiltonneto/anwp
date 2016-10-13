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
require_once 'AddThisFollowButtonsToolParent.php';

if (!class_exists('AddThisFollowButtonsCustomTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Custom Follow Button tools
     *
     * @category   FollowButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisFollowButtonsCustomTool extends AddThisFollowButtonsToolParent
    {
        public $layersClass = 'addthis_custom_follow';
        public $prettyName = 'Custom Follow Buttons';

        public $edition = 'pro';
        public $anonymousSupport = false;
        public $inline = true;

        public $widgetClassName = 'AddThisFollowButtonsCustomWidget';
        public $widgetBaseId = 'addthis_custom_follow_widget';
        public $widgetName = 'Follow Buttons - Custom';
        public $widgetDescription = 'These custom follow buttons from AddThis allow you to match the look and feel of your website.';
        public $defaultWidgetTitle = 'Follow Me';

        public $shortCode = 'addthis_custom_follow_buttons';
    }
}