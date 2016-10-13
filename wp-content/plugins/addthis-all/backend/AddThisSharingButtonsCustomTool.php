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

if (!class_exists('AddThisSharingButtonsCustomTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Custom Sharing Button tools
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsCustomTool extends AddThisSharingButtonsToolParent
    {
        public $layersClass = 'addthis_custom_sharing';
        public $prettyName = 'Custom Sharing Buttons';

        public $edition = 'pro';
        public $anonymousSupport = false;
        public $inline = true;

        public $widgetClassName = 'AddThisSharingButtonsCustomWidget';
        public $widgetBaseId = 'addthis_custom_sharing_widget';
        public $widgetName = 'Sharing Buttons - Custom';
        public $widgetDescription = 'These custom sharing buttons from AddThis allow you to match the look and feel of your website.';
        public $defaultWidgetTitle = 'Share';

        public $shortCode = 'addthis_custom_sharing_buttons';
    }
}