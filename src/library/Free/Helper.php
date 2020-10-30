<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSEmbed.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSEmbed\Free;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

abstract class Helper
{
    /**
     * @var string
     */
    protected static $minPHPVersion = '5.6';

    /**
     * @var bool
     */
    protected static $systemRequirements = null;

    /**
     * @return bool
     */
    public static function isDebugEnabled()
    {
        $plugin = PluginHelper::getPlugin('content', 'osembed');
        $params = new Registry($plugin ? $plugin->params : null);

        $params    = $params ?: new Registry();
        $appParams = Factory::getConfig();

        return $params->get('debug') || $appParams->get('debug');
    }

    /**
     * @return void
     */
    public static function addLogger()
    {
        if (static::isDebugEnabled()) {
            Log::addLogger(
                ['text_file' => 'osembed.log.php'],
                Log::ALL,
                ['osembed.library', 'osembed.content', 'osembed.system']
            );
        }
    }

    /**
     * @return bool
     */
    public static function complySystemRequirements()
    {
        if (static::$systemRequirements === null) {
            static::$systemRequirements = version_compare(phpversion(), static::$minPHPVersion, 'ge');

            if (!static::$systemRequirements) {
                $message = Text::sprintf(
                    'PLG_CONTENT_OSEMBED_ERROR_PHP_VERSION',
                    static::$minPHPVersion,
                    phpversion()
                );

                Factory::getApplication()->enqueueMessage($message);
                Log::add($message, Log::ERROR, 'osembed.library');
            }
        }

        return static::$systemRequirements;
    }
}
