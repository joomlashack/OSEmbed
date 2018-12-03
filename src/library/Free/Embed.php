<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free;

defined('_JEXEC') or die();

use Embera\Embera;
use Embera\Formatter;
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
            $data = static::$embera->getUrlInfo($content);

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
                $processor = new \Embera\HtmlProcessor(static::getIgnoreTags(), $table);
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
