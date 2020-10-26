<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2020 Joomlashack.com. All rights reserved
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

namespace Alledia\OSEmbed\Free;

use Joomla\CMS\Log\Log;

defined('_JEXEC') or die();

abstract class Helper
{
    protected static $minPHPVersion = '5.6';

    public static function addLog()
    {
        Log::addLogger(
            array(
                // Sets file name
                'text_file' => 'osembed.log.php'
            ),
            Log::ALL,
            array('osembed.library', 'osembed.content', 'osembed.system')
        );
    }

    public static function complyBasicRequirements($logWarnings = false)
    {
        $complies = true;

        // PHP Version
        $version = phpversion();
        if (version_compare($version, static::$minPHPVersion, 'lt')) {
            $complies = false;

            if ($logWarnings) {
                Log::add(
                    'OSEmbed requires PHP ' . static::$minPHPVersion . ' or later. You are running the ' . $version,
                    Log::WARNING,
                    'osembed.library'
                );
            }
        }

        return $complies;
    }
}
