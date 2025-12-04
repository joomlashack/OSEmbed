<?php

/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2026 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

try {
    $frameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';
    if (is_file($frameworkPath) == false || (include $frameworkPath) == false) {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage('[OSEmbed] Joomlashack framework not found', 'error');
        }

        return false;
    }

    if (defined('ALLEDIA_FRAMEWORK_LOADED') && !defined('OSEMBED_LOADED')) {
        define('OSEMBED_PLUGIN_PATH', __DIR__);
        define('OSEMBED_LIBRARY', OSEMBED_PLUGIN_PATH . '/library');

        require_once OSEMBED_LIBRARY . '/autoload.php';
        //Helper::loadLibrary('plg_content_osembed');

        define('OSEMBED_LOADED', 1);
    }

} catch (Throwable $error) {
    Factory::getApplication()
        ->enqueueMessage('[OSEmbed] Unable to initialize: ' . $error->getMessage(), 'error');

    return false;
}

return defined('ALLEDIA_FRAMEWORK_LOADED') && defined('OSEMBED_LOADED');
