<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Alledia\Framework\Factory;
use Alledia\OSEmbed\Free\Helper;
use Alledia\OSEmbed\Free\Embed;

require_once 'include.php';

/**
 * OSEmbed Content Plugin
 */
class PlgContentOSEmbed extends AbstractPlugin
{
    protected $namespace = 'OSEmbed';

    protected $allowedToRun = true;

    /**
     * Constructor
     *
     * @param   object  &$subject  The object to observe
     * @param   array   $config    An optional associative array of configuration settings.
     *                             Recognized key values include 'name', 'group', 'params', 'language'
     *                             (this list is not meant to be comprehensive).
     *
     * @since   1.5
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        $this->init();

        // Check the minumum requirements
        if (!Helper::complyBasicRequirements()) {
            $this->allowedToRun = false;
        }
    }

    /**
     * Plugin that loads module positions within content
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$article  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  mixed   true if there is an error. Void otherwise.
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed
        if ($context == 'com_finder.indexer' || !$this->allowedToRun) {
            return true;
        }

        $doc = Factory::getDocument();
        $doc->addStyleSheet('media/plg_content_osembed/css/osembed.css');

        $article->text = Embed::parseContent($article->text);
    }
}
