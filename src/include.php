<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\Framework;

defined('_JEXEC') or die();

define('OSEMBED_PLUGIN_PATH', __DIR__);

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            $app->enqueueMessage('[OSEmbed] Alledia framework not found', 'error');
        }
    }
}

if (defined('ALLEDIA_FRAMEWORK_LOADED') && !defined('OSEMBED_LOADED')) {
    include_once 'library/autoload.php';

    Framework\Joomla\Extension\Helper::loadLibrary('plg_content_osembed');

    define('OSEMBED_LOADED', 1);
}
