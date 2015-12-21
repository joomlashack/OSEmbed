<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free;

defined('_JEXEC') or die();

use Embera;

jimport('joomla.log.log');


abstract class Embed
{
    public static function parseContent($content, $stripNewLine = false)
    {
        if (!empty($content)) {
            $embera = new Embera\Embera();
            $embera->addProvider('facebook.com', 'Alledia\OSEmbed\Free\Provider\Facebook');
            $content = $embera->autoEmbed($content);
        }

        if ($stripNewLine) {
            $content = preg_replace('/\n/', '', $content);
        }

        return $content;
    }
}
