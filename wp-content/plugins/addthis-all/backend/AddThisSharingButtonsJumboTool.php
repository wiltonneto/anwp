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

if (!class_exists('AddThisSharingButtonsJumboTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Jumbo Counter Sharing Button tools
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsJumboTool extends AddThisSharingButtonsToolParent
    {
        public $layersClass = 'addthis_jumbo_share';
        public $prettyName = 'Jumbo Share Counter';

        public $edition = 'pro';
        public $anonymousSupport = false;
        public $inline = true;

        public $widgetClassName = 'AddThisSharingButtonsJumboWidget';
        public $widgetBaseId = 'addthis_jumbo_share_widget';
        public $widgetName = 'Sharing Buttons - Jumbo Counter';
        public $widgetDescription = 'Large share counter buttons from AddThis to showcase how many shares your content has received.';
        public $defaultWidgetTitle = 'Share';

        public $shortCode = 'addthis_jumbo_sharing_buttons';
    }
}