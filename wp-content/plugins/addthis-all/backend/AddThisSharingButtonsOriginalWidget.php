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

if (!class_exists('AddThisSharingButtonsOriginalWidget')) {
    /**
     * WordPress widget class for AddThis Original Sharing Buttons
     *
     * @category   SharingButtons
     * @package    AddThisWordPress
     * @subpackage Tools\Widgets
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisSharingButtonsOriginalWidget extends AddThisWidget
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
    }
}