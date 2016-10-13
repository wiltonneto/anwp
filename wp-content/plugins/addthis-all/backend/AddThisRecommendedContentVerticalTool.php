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
require_once 'AddThisRecommendedContentToolParent.php';

if (!class_exists('AddThisRecommendedContentVerticalTool')) {
    /**
     * A class with various special configs and functionality for
     * AddThis Vertical Recommended Content tools
     *
     * @category   RecommendedContent
     * @package    AddThisWordPress
     * @subpackage Tools
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisRecommendedContentVerticalTool extends AddThisRecommendedContentToolParent
    {
        public $layersClass = 'addthis_recommended_vertical';
        public $prettyName = 'Vertical Recommended Content';

        public $edition = 'basic';
        public $anonymousSupport = false;
        public $inline = true;

        public $widgetClassName = 'AddThisRecommendedContentVerticalWidget';
        public $widgetBaseId = 'addthis_recommended_vertical_widget';
        public $widgetName = 'Related Posts - Vertical';
        public $widgetDescription = 'Vertical recommended content from AddThis to showcase trending or related content and images to keep visitors engaged and on your site.';
        public $defaultWidgetTitle = 'Recommended Content';

        public $shortCode = 'addthis_vertical_recommended_content';
    }
}