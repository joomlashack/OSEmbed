<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Extension\AbstractPlugin;
use Alledia\OSEmbed\Free\Embera;
use Alledia\OSEmbed\Free\Helper;
use Embera\ProviderCollection\CustomProviderCollection;
use Embera\ProviderCollection\ProviderCollectionAdapter;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

if (include 'include.php') {
    class Plgcontentosembed extends AbstractPlugin
    {
        protected $namespace = 'OSEmbed';

        /**
         * @var string
         */
        public $type = 'content';

        protected $autoloadLanguage = true;

        /**
         * @var SiteApplication
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
         * @inheritDoc
         */
        public function __construct(&$subject, $config = [])
        {
            parent::__construct($subject, $config);

            $this->init();
        }

        /**
         * @inheritDoc
         */
        protected function init()
        {
            parent::init();

            $this->callHelper('addLogger');

            if ($this->isEnabled()) {
                $this->params->def('responsive', true);
                $this->params->def('ignore_tags', ['pre', 'code', 'a', 'img', 'iframe']);

                $this->debug = $this->params->get('debug', false);
            }
        }

        /**
         * @param string   $context
         * @param object   $article
         * @param Registry $params
         *
         * @return  void
         * @throws Exception
         */
        public function onContentPrepare($context, $article, $params)
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
                        if ($params && $params->get('show_description')) {
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
         * @return string[]
         */
        public function onOsembedProviders(): array
        {
            try {
                $providerList = $this->getProviderList();

                $providersProperty = new ReflectionProperty($providerList, 'providers');
                $providersProperty->setAccessible(true);

                return $providersProperty->getValue($providerList);

            } catch (Throwable $error) {
                $message = Text::sprintf('PLG_CONTENT_OSEMBED_ERROR_PROVIDERS', $error->getMessage());
                Log::add($message, Log::ERROR, Helper::LOG_SYSTEM);
                if (Helper::isDebugEnabled()) {
                    $this->app->enqueueMessage($message, 'error');
                }
            }

            return [];
        }

        /**
         * @return Embera
         * @throws Exception
         */
        protected function getEmbera(): Embera
        {
            if ($this->embera === null) {
                $config = $this->params->toArray();

                if (!is_array($config['ignore_tags'])) {
                    $config['ignore_tags'] = array_filter(
                        array_unique(
                            array_map('trim', explode(',', $config['ignore_tags']))
                        )
                    );
                }

                $this->embera = new Embera($config, $this->getProviderList(), null, $this->params);
            }

            return $this->embera;
        }

        /**
         * @return ProviderCollectionAdapter
         */
        protected function getProviderList(): ProviderCollectionAdapter
        {
            $className = sprintf(
                '\\Alledia\\OSEmbed\\%s\\ProviderCollection',
                $this->isPro() ? 'Pro' : 'Free'
            );

            if (class_exists($className)) {
                return new $className(['params' => $this->params]);
            }

            return new CustomProviderCollection();
        }

        /**
         * @param ?string $content
         *
         * @return ?string
         * @throws Exception
         */
        protected function parseContent(?string $content): ?string
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
         * @return bool
         */
        protected function isEnabled(): bool
        {
            if ($this->enabled === null) {
                $isHTML = in_array(Factory::getDocument()->getType(), ['html', 'raw']);

                $this->enabled = $isHTML && $this->callHelper('complySystemRequirements');
            }

            return $this->enabled;
        }

        /**
         * @param string $method
         * @param array  $arguments
         *
         * @return mixed
         */
        protected function callHelper(string $method, array $arguments = [])
        {
            $helper = sprintf(
                '\\Alledia\\OSEmbed\\%s\\Helper',
                $this->isPro() ? 'Pro' : 'Free'
            );

            try {
                $callable = [$helper, $method];
                if (is_callable($callable)) {
                    return call_user_func_array($callable, $arguments);
                }

            } catch (Throwable $error) {
                $message = Text::sprintf('PLG_CONTENT_OSEMBED_ERROR_HELPER_METHOD', $helper, $method);
                Log::add($message, Log::ERROR, Helper::LOG_LIBRARY);

                if (Helper::isDebugEnabled()) {
                    $this->app->enqueueMessage($message, 'error');
                }
            }

            return null;
        }
    }
}
