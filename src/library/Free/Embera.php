<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2024 Joomlashack.com. All rights reserved
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

namespace Alledia\OSEmbed\Free;

use Alledia\Framework\Factory;
use Embera\Http\HttpClientInterface;
use Embera\Http\OembedClient;
use Embera\Provider\ProviderInterface;
use Embera\ProviderCollection\ProviderCollectionInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class Embera extends \Embera\Embera
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function __construct(
        array $config = [],
        ProviderCollectionInterface $collection = null,
        HttpClientInterface $httpClient = null,
        Registry $params = null
    ) {
        $this->app    = Factory::getApplication();
        $this->params = $params ?: new Registry();

        parent::__construct($config, $collection, $httpClient);
    }

    /**
     * @inheritDoc
     */
    public function getUrlData($urls)
    {
        $return = parent::getUrlData($urls);

        $this->displayProviderInfo($this->providerCollection->findProviders($urls), $return);

        if ($this->params->get('debug') && $this->hasErrors()) {
            while ($error = array_pop($this->errors)) {
                $this->app->enqueueMessage('<p>' . $error . '</p>', 'error');
                Log::add($error, Log::ERROR, 'osembed.content');
            }
        }

        return $return;
    }

    /**
     * @param ProviderInterface[] $providers
     * @param array               $urlData
     *
     * @return void
     */
    protected function displayProviderInfo(array $providers, array $urlData)
    {
        if ($this->params->get('debug')) {
            $oembedClient = new OembedClient($this->config, $this->httpClient);

            try {
                $constructUrl = new \ReflectionMethod($oembedClient, 'constructUrl');
                $constructUrl->setAccessible(true);

                $itemTemplate = '<li><div>%s: </div><div>%s</div></li>';
                foreach ($providers as $found => $provider) {
                    $url = null;
                    if ($constructUrl) {
                        $url = urldecode(
                            $constructUrl->invokeArgs(
                                $oembedClient,
                                [$provider->getEndpoint(), $provider->getParams()]
                            )
                        );
                    }

                    if (isset($urlData[$found]['embera_using_fake_response'])) {
                        $fakeResponse = $urlData[$found]['embera_using_fake_response'] ? 'YES' : 'NO';

                    } else {
                        $fakeResponse = '*';
                    }

                    $this->app->enqueueMessage(
                        sprintf(
                            '<div class="osembed-debug"><span>%s</span><ul>%s%s%s%s</ul></div>',
                            $provider->getProviderName(),
                            sprintf($itemTemplate, 'Found', $found),
                            sprintf($itemTemplate, 'Endpoint', $provider->getEndpoint()),
                            sprintf($itemTemplate, 'URL', $url ? $url : '*error*'),
                            sprintf($itemTemplate, 'Fake Rsp', $fakeResponse)
                        ),
                        'notice'
                    );
                }

            } catch (\Exception $error) {
                $this->app->enqueueMessage('Error: ' . $error->getMessage(), 'notice');
            }
        }
    }
}
