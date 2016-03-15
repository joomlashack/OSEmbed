<?php
/**
 * @package   OSEmbed
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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

    Framework\AutoLoader::register('Alledia\OSEmbed', OSEMBED_PLUGIN_PATH . '/library');

    if (class_exists('Alledia\OSEmbed\Free\Embed')) {
        define('OSEMBED_LOADED', 1);
    }
}
