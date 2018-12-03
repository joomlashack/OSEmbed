<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use JFactory;
use JLog;
use JRegistry;
use JText;
use WFEditor;

jimport('joomla.log.log');


abstract class Helper
{
    protected static $minPHPVersion = '5.3';

    public static function addLog()
    {
        JLog::addLogger(
            array(
                // Sets file name
                'text_file' => 'osembed.log.php'
            ),
            JLog::ALL,
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
                JLog::add(
                    'OSEmbed requires PHP ' . static::$minPHPVersion . ' or later. You are running the ' . $version,
                    JLog::WARNING,
                    'osembed.library'
                );
            }
        }

        return $complies;
    }
}
