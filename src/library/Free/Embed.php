<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free;

defined('_JEXEC') or die();

use Embera\Embera;
use Embera\Formatter;

jimport('joomla.log.log');


abstract class Embed
{
    protected static $embera;

    public static function parseContent($content, $stripNewLine = false)
    {
        if (!isset(static::$embera)) {
            static::$embera = new Formatter(new Embera, true);
        }

        if (!empty($content)) {
            static::$embera->addProvider('facebook.com', '\\Alledia\\OSEmbed\\Free\\Provider\\Facebook');

            // Add wrapper to make the embed responsive
            static::$embera->setTemplate('<div class="osembed-wrapper ose-{provider_alias} {wrapper_class}">{html}</div>');

            $content = static::$embera->transform($content);

            if ($stripNewLine) {
                $content = preg_replace('/\n/', '', $content);
            }
        }

        return $content;
    }

    public static function onContentBeforeSave($article)
    {
        return true;
    }
}
