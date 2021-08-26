<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSEmbed.
 *
 * OSEmbed is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSEmbed is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSEmbed.  If not, see <http://www.gnu.org/licenses/>.
 */

use Alledia\Framework;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

if (!defined('OSEMBED_LOADED')) {
    $frameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';
    if (is_file($frameworkPath) && include $frameworkPath) {
        include_once 'library/autoload.php';

        Framework\Joomla\Extension\Helper::loadLibrary('plg_content_osembed');

        define('OSEMBED_LOADED', 1);
        define('OSEMBED_PLUGIN_PATH', __DIR__);

    } else {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage('[OSEmbed] Joomlashack framework not found', 'error');
        }
    }
}

return defined('OSEMBED_LOADED');
