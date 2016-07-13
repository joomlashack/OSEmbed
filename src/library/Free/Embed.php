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

    protected static $ignoreTags;

    public static function parseContent($content, $stripNewLine = false)
    {
        if (!empty($content)) {
            // Initialise the Embera library
            if (!isset(static::$embera)) {
                static::$embera = new Embera;
            }

            // Add additional providers
            static::$embera->addProvider('facebook.com', '\\Alledia\\OSEmbed\\Free\\Provider\\Facebook');

            // Get all the supported URLs and respective info
            $data = static::$embera->getUrlInfo($content);

            // Get a list of URLs and the final HTML code
            $table = array();
            foreach ($data as $url => $service) {
                $html = $service['html'];

                if (!empty($html)) {

                    $providerClass = \JArrayHelper::getValue($service, 'provider_alias', 'default');
                    $wrapperClass  = \JArrayHelper::getValue($service, 'wrapper_class', 'default');

                    // Wrapper the HTML code to make the embed responsive
                    $table[$url] = "<div class=\"osembed_wrapper ose-{$providerClass} {$wrapperClass}\">{$html}</div>";
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
    protected static function getIgnoreTags()
    {
        if (!isset(static::$ignoreTags)) {
            static::$ignoreTags = array('pre', 'code', 'a', 'img', 'iframe');
        }

        return static::$ignoreTags;
    }
}
