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
use Alledia\OSEmbed\Free\Embera;
use Embera\ProviderCollection\CustomProviderCollection;
use Embera\ProviderCollection\ProviderCollectionAdapter;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;
use Joomla\Event\Dispatcher;
use Joomla\Registry\Registry;

include_once 'include.php';
if (!defined('OSEMBED_LOADED')) {
    return;
}

class Plgcontentosembed extends AbstractPlugin
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
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var bool
     */
    protected $enabled = null;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var Embera
     */
    protected $embera = null;

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

            $this->params->def('responsive', true);
            $this->params->def('ignore_tags', ['pre', 'code', 'a', 'img', 'iframe']);
            $this->params->def('exclude_urls', ['youtu.be']);

            $this->debug = $this->params->get('debug', false);

            $excludeUrls = $this->params->get('exclude_urls');
            if (!is_array($excludeUrls)) {
                $excludeUrls = array_filter(array_unique(explode(',', $excludeUrls)));
                $this->params->set('exclude_urls', array_map('trim', $excludeUrls));
            }
        }
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
        if ($this->isEnabled() && !in_array($context, $this->excludedContexts)) {
            $versionUID = md5($this->extension->getVersion());

            HTMLHelper::_('jquery.framework');

            HTMLHelper::_(
                'stylesheet',
                'plg_content_osembed/osembed.css',
                ['relative' => true, 'version' => $versionUID]
            );

            HTMLHelper::_(
                'script',
                'plg_content_osembed/osembed.min.js',
                ['relative' => true, 'version' => $versionUID]
            );

            $textField = null;
            switch ($context) {
                case 'com_content.category':
                    if ($this->params->get('show_intro') && isset($article->introtext)) {
                        $textField = 'introtext';
                    }
                    break;

                case 'com_content.categories':
                    if ($params->get('show_description')) {
                        $textField = 'text';
                    }
                    break;

                case 'com_content.category.title':
                    // disable these
                    break;

                default:
                    $textField = 'text';
                    break;
            }

            if ($this->debug) {
                $this->app->enqueueMessage(sprintf('%s: Field=%s', $context, $textField ?: 'null'), 'notice');
            }

            if ($textField && isset($article->{$textField})) {
                $article->{$textField} = $this->parseContent($article->{$textField});
            }
        }
    }

    /**
     * @return object
     */
    public function onOsembedProviders()
    {
        try {
            $providerList = $this->getProviderList();

            $providersProperty = new ReflectionProperty($providerList, 'providers');
            $providersProperty->setAccessible(true);

            $providers = $providersProperty->getValue($providerList);

            return (object)[
                'providers'    => $providers,
                'excludeHosts' => $this->params->get('exclude_urls')
            ];

        } catch (Exception $error) {
            // Ignore
        }

        return null;
    }

    /**
     * @return Embera
     */
    protected function getEmbera()
    {
        if ($this->embera === null) {
            $config = [
                'responsive'  => (bool)$this->params->get('responsive'),
                'ignore_tags' => (array)$this->params->get('ignore_tags')
            ];

            $this->embera = new Embera($config, $this->getProviderList(), null, $this->params);
        }

        return $this->embera;
    }

    /**
     * @return ProviderCollectionAdapter
     */
    protected function getProviderList()
    {
        $className = sprintf(
            '\\Alledia\\OSEmbed\\%s\\ProviderCollection',
            $this->isPro() ? 'Pro' : 'Free'
        );
        if (class_exists($className)) {
            return new $className();
        }

        return new CustomProviderCollection();
    }

    /**
     * @param string $content
     *
     * @return string
     * @throws Exception
     */
    protected function parseContent($content)
    {
        $embera = $this->getEmbera();
        if ($content && $embera) {
            if ($this->params->get('stripnewline', false)) {
                return preg_replace('/\n/', '', $embera->autoEmbed($content));

            } else {
                return $embera->autoEmbed($content);
            }
        }

        return $content;
    }

    /**
     * @return void
     */
    protected function addLogger()
    {
        if ($this->params->get('debug') || $this->app->get('debug')) {
            $this->params->set('debug', true);

            Log::addLogger(
                ['text_file' => 'osembed.log.php'],
                Log::ALL,
                ['osembed.library', 'osembed.content', 'osembed.system']
            );
        }
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        if ($this->enabled === null) {
            $this->enabled = true;

            $option  = $this->app->input->get('option');
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
