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

defined('_JEXEC') or die;

use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Alledia\OSEmbed\Free\Embed;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\Event\Dispatcher;
use Joomla\Registry\Registry;

include_once 'include.php';
if (!defined('OSEMBED_LOADED')) {
    return;
}

class PlgContentOSEmbed extends AbstractPlugin
{
    protected $namespace = 'OSEmbed';

    /**
     * @var string
     */
    public $type = 'content';

    /**
     * @var string
     */
    protected $minPHPVersion = '5.6';

    /**
     * @var bool
     */
    protected $enabled = null;

    /**
     * @var string[]
     */
    protected $excludedContexts = [
        'com_finder.indexer',
        'com_search.search'
    ];

    /**
     * PlgContentOSEmbed constructor.
     *
     * @param Dispatcher $subject
     * @param array      $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);

        $this->addLogger();

        if ($this->isEnabled()) {
            $this->init();
        }
    }

    /**
     * @return Embed
     */
    protected function getEmbed()
    {
        $className = sprintf(
            '\\Alledia\\OSEmbed\\%s\\Embed',
            $this->isPro() ? 'Pro' : 'Free'
        );

        if (class_exists($className)) {
            return new $className($this->params);
        }

        return null;
    }

    /**
     * @param string   $context
     * @param object   $article
     * @param Registry $params
     * @param int      $page
     *
     * @return  void
     * @throws Exception
     */
    public function onContentPrepare($context, $article, $params, $page = 0)
    {
        if ($this->isEnabled() || in_array($context, $this->excludedContexts)) {
            $versionUID = md5($this->extension->getVersion());

            HTMLHelper::_('jquery.framework');

            HTMLHelper::_(
                'stylesheet',
                'plg_content_osembed/osembed.css',
                ['relative' => true, 'version' => $versionUID]
            );

            HTMLHelper::_(
                'script',
                'plg_content_osembed/osembed.js',
                ['relative' => true, 'version' => $versionUID]
            );

            $article->text = $this->getEmbed()->parseContent($article->text, false);
        }
    }

    /**
     * @param string $context
     * @param object $article
     * @param bool   $isNew
     *
     * @return bool
     */
    public function onContentBeforeSave($context, $article, $isNew)
    {
        if ($embed = $this->getEmbed()) {
            return $this->getEmbed()->onContentBeforeSave($article);
        }

        return true;
    }

    /**
     * @return void
     */
    protected function addLogger()
    {
        Log::addLogger(
            ['text_file' => 'osembed.log.php'],
            Log::ALL,
            ['osembed.library', 'osembed.content', 'osembed.system']
        );
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        if ($this->enabled === null) {
            $this->enabled = true;

            $option  = Factory::getApplication()->input->get('option');
            $docType = Factory::getDocument()->getType();
            $version = phpversion();

            // Do not run if called from OSMap's XML view
            if ($option === 'com_osmap' && $docType !== 'html') {
                $this->enabled = false;
            }

            if (version_compare($version, $this->minPHPVersion, 'lt')) {
                $this->enabled = false;

                Log::add(
                    sprintf(
                        'OSEmbed requires PHP %s or later. You are running VERSION %S',
                        $this->minPHPVersion,
                        $version
                    ),
                    Log::WARNING,
                    'osembed.library'
                );
            }
        }

        return $this->enabled;
    }
}
