<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2024 Joomlashack.com. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

abstract class Helper
{
    public const LOG_LIBRARY = 'osembed.library';
    public const LOG_CONTENT = 'osembed.content';
    public const LOG_SYSTEM  = 'osembed.system';

    /**
     * @var string
     */
    protected static $minPHPVersion = '7.4';

    /**
     * @var bool
     */
    protected static $systemRequirements = null;

    /**
     * @return bool
     */
    public static function isDebugEnabled(): bool
    {
        $plugin = PluginHelper::getPlugin('content', 'osembed');
        $params = new Registry($plugin ? $plugin->params : null);

        $appParams = Factory::getConfig();

        return $params->get('debug') || $appParams->get('debug');
    }

    /**
     * @return void
     */
    public static function addLogger(): void
    {
        if (static::isDebugEnabled()) {
            Log::addLogger(
                ['text_file' => 'osembed.log.php'],
                Log::ALL,
                [static::LOG_CONTENT, static::LOG_LIBRARY, static::LOG_SYSTEM]
            );
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function complySystemRequirements(): bool
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
                Log::add($message, Log::ERROR, static::LOG_LIBRARY);
            }
        }

        return static::$systemRequirements;
    }
}
