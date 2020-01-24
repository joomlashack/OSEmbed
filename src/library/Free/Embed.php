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

defined('_JEXEC') or die();

use Embera\HtmlProcessor;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.log.log');

abstract class Embed
{
    /**
     * @var Embera
     */
    protected static $embera = null;

    /**
     * @var string[]
     */
    protected static $ignoreTags = null;

    /**
     * @return Embera
     */
    protected static function getEmbera()
    {
        if (static::$embera === null) {
            static::$embera = new Embera();

            if (static::class === self::class) {
                // Disable some services we're not supporting in Free version
                static::$embera->addProvider('alpha.app.net', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('c9ng.com', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.org.uk', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.co.uk', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.ie', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('youtu.be', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
            }
        }

        return static::$embera;
    }

    /**
     * @param string $content
     * @param bool   $stripNewLine
     *
     * @return string
     * @throws \Exception
     */
    public static function parseContent($content, $stripNewLine = false)
    {
        if (!empty($content)) {
            // Get all the supported URLs and respective info
            $data = static::getEmbera()->getUrlInfo($content);

            // Get a list of URLs and the final HTML code
            $table = array();
            foreach ($data as $url => $service) {
                if (isset($service['html'])) {
                    $html = $service['html'];

                    if (!empty($html)) {
                        $providerClass = ArrayHelper::getValue($service, 'provider_alias', 'default');
                        $wrapperClass  = ArrayHelper::getValue($service, 'wrapper_class', 'default');

                        // Wrapper the HTML code to make the embed responsive
                        $table[$url] = sprintf(
                            '<div class="%s">%s</div>',
                            "osembed_wrapper ose-{$providerClass} {$wrapperClass}",
                            $html
                        );
                    }
                }
            }

            // Determine wether the body looks like HTML or just plain text.
            if (strpos($content, '>') !== false) {
                $processor = new HtmlProcessor(static::getIgnoreTags(), $table);
                $content   = $processor->process($content);

            } else {
                // Replace the URLs
                $content = strtr($content, $table);
            }

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

    /**
     * Get the list of tags to ignore
     *
     * @return array
     */
    public static function getIgnoreTags()
    {
        if (!isset(static::$ignoreTags)) {
            static::$ignoreTags = array('pre', 'code', 'a', 'img', 'iframe');
        }

        return static::$ignoreTags;
    }
}
