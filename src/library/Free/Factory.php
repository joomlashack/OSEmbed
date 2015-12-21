<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free;

use Alledia\Framework\Joomla\Extension\Generic;

defined('_JEXEC') or die();


abstract class Factory
{
    protected static $plugin;

    public static function getPlugin()
    {
        if (empty(static::$plugin)) {
            static::$plugin = new Generic('osembed', 'plugin', 'system');
        }

        return static::$plugin;
    }

    public static function getParams()
    {
        $plugin = static::getPlugin();

        return $plugin->params;
    }
}
