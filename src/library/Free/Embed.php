<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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

use Embera\Embera;
use Embera\HtmlProcessor;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.log.log');

abstract class Embed
{
    /**
     * @var Embera
     */
    protected static $embera;

    /**
     * @var string[]
     */
    protected static $ignoreTags;

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
            // Initialise the Embera library
            if (!isset(static::$embera)) {
                static::$embera = new Embera;

                // Disable some unsupported services to avoid warnings
                static::$embera->addProvider('alpha.app.net', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('c9ng.com', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.org.uk', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.co.uk', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('geograph.ie', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
                static::$embera->addProvider('youtu.be', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
            }

            // Get all the supported URLs and respective info
            $data = static::getUrlInfo($content);

            // Get a list of URLs and the final HTML code
            $table = array();
            foreach ($data as $url => $service) {
                if (isset($service['html'])) {
                    $html = $service['html'];

                    if (!empty($html)) {
                        $providerClass = ArrayHelper::getValue($service, 'provider_alias', 'default');
                        $wrapperClass  = ArrayHelper::getValue($service, 'wrapper_class', 'default');

                        // Wrapper the HTML code to make the embed responsive
                        $table[$url] = "<div class=\"osembed_wrapper ose-{$providerClass} {$wrapperClass}\">{$html}</div>";
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

    /**
     * @param string|array $content
     *
     * @return array[]
     * @throws \ReflectionException
     */
    protected static function getUrlInfo($content)
    {
        $providers = static::$embera ? static::$embera->getUrlInfo($content) : array();

        // Check if we don't have a provider_name set, and set it based on the class name
        foreach ($providers as $url => &$service) {
            if (!isset($service['provider_name'])) {
                $reflect                  = new \ReflectionClass($service);
                $service['provider_name'] = $reflect->getShortName();
                unset($reflect);
            }

            // Add the provider_alias if not exists
            if (!isset($service['provider_alias'])) {
                $service['provider_alias'] = preg_replace('/[^a-z0-9\-]/i', '-', $service['provider_name']);
                $service['provider_alias'] = strtolower(str_replace('--', '-', $service['provider_alias']));
            }

            // Add the wrapper_class if not exists
            if (!isset($service['wrapper_class'])) {
                $service['wrapper_class'] = '';
            }
        }

        return $providers;
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
