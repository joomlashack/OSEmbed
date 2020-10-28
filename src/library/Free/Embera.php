<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2020 Joomlashack.com. All rights reserved
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
     * @var string[]
     */
    protected $excludeUrls = [];

    /**
     * @inheritDoc
     */
    public function __construct(
        array $config = [],
        ProviderCollectionInterface $collection = null,
        HttpClientInterface $httpClient = null,
        Registry $params = null
    ) {

        $this->app         = Factory::getApplication();
        $this->params      = $params ?: new Registry();
        $this->excludeUrls = array_filter((array)$this->params->get('exclude_urls'));

        parent::__construct($config, $collection, $httpClient);
    }

    /**
     * @inheritDoc
     */
    public function getUrlData($urls)
    {
        $return = parent::getUrlData($urls);

        if ($this->params->get('debug') && $this->hasErrors()) {
            while ($error = array_pop($this->errors)) {
                Factory::getApplication()->enqueueMessage('<p>' . $error . '</p>', 'error');
                Log::add($error, Log::ERROR, 'osembed.content');
            }
        }

        return array_filter($return, [$this, 'filterExcluded'], ARRAY_FILTER_USE_KEY);
    }

    /**
     * For use by array_filter() with ARRAY_FILTER_USE_KEY flag
     *
     * @param string $url
     *
     * @return bool
     */
    protected function filterExcluded($url)
    {
        foreach ($this->excludeUrls as $excludeUrl) {
            if (preg_match('#' . preg_quote($excludeUrl, '#') . '#', $url)) {
                if ($this->params->get('debug')) {
                    $this->app->enqueueMessage(
                        sprintf(
                            '%s urls are disabled - %s',
                            $excludeUrl,
                            $url
                        ),
                        'notice'
                    );
                }
                return false;
            }
        }

        return true;
    }
}
