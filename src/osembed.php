<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2024 Joomlashack.com. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

if (include 'include.php') {
    class Plgcontentosembed extends AbstractPlugin
    {
        /**
         * @inheritdoc
         */
        protected $namespace = 'OSEmbed';

        /**
         * @var string
         */
        public $type = 'content';

        /**
         * @inheritdoc
         */
        protected $autoloadLanguage = true;

        /**
         * @inheritdoc
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
            'com_search.search',
            'com_content.category.title',
        ];

        /**
         * @var string[]
         */
        protected $ignoreViews = [];

        /**
         * @var string[]
         */
        protected $defaultViews = [
            'com_content:category:list' => 'com_content:category',
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

                $this->debug = (bool)$this->params->get('debug', false);

                $ignoreViews = $this->params->get('ignore_views') ?: [];
                foreach ($ignoreViews as $ignoreView => $enabled) {
                    if ($enabled) {
                        $this->ignoreViews[] = $ignoreView;
                        if ($this->defaultViews[$ignoreView] ?? null) {
                            $this->ignoreViews[] = $this->defaultViews[$ignoreView];
                        }
                    }
                }
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
        public function onContentPrepare($context, $article, $params): void
        {
            if ($this->isEnabled() && $this->isNotExcluded($context)) {
                $versionUid = md5($this->extension->getVersion());

                HTMLHelper::_('jquery.framework');

                HTMLHelper::_(
                    'stylesheet',
                    'plg_content_osembed/osembed.css',
                    ['relative' => true, 'version' => $versionUid]
                );

                HTMLHelper::_(
                    'script',
                    'plg_content_osembed/osembed.min.js',
                    ['relative' => true, 'version' => $versionUid]
                );

                $textField = null;
                switch ($context) {
                    case 'com_content.category':
                        if ($params->get('show_intro') && isset($article->text)) {
                            $textField = 'text';
                        }
                        break;

                    case 'com_content.categories':
                        if ($params->get('show_description')) {
                            $textField = 'text';
                        }
                        break;

                    default:
                        $textField = 'text';
                        break;
                }

                if ($this->debug) {
                    $this->app->enqueueMessage(
                        sprintf(
                            '%s: Field=%s (%s)',
                            $context,
                            $textField ?: 'null',
                            isset($article->{$textField}) ? 'True' : 'False'
                        ),
                        'notice'
                    );
                }

                if ($textField && isset($article->{$textField})) {
                    $article->{$textField} = $this->parseContent($article->{$textField});
                }
            }
        }

        /**
         * @param string $context
         *
         * @return bool
         */
        protected function isNotExcluded(string $context): bool
        {
            $currentView = join(
                ':',
                array_filter(
                    [
                        $this->app->input->get('option'),
                        $this->app->input->get('view'),
                        $this->app->input->get('layout'),
                    ]
                )
            );

            $excluded = in_array($context, $this->excludedContexts)
                || in_array($currentView, $this->ignoreViews);

            if ($this->debug && $excluded) {
                $this->app->enqueueMessage(
                    sprintf(
                        'Ignoring Context %s / View %s',
                        $context,
                        $currentView
                    ),
                    'notice'
                );

            }

            return $excluded == false;
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

                if (is_array($config['ignore_tags']) == false) {
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
            if ($content) {
                $embera = $this->getEmbera();

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
                $isHtml = in_array($this->app->getDocument()->getType(), ['html', 'raw']);

                $this->enabled = $isHtml && $this->callHelper('complySystemRequirements');
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
