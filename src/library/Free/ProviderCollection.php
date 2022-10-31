<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020-2021 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSEmbed.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSEmbed\Free;

use Embera\ProviderCollection\ProviderCollectionAdapter;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class ProviderCollection extends ProviderCollectionAdapter
{
    /**
     * @var string[]
     */
    protected $excludeUrls = [
        'youtu.be'
    ];

    /**
     * List based on Embera\ProviderCollection\SlimProviderCollection
     *
     * @var string[]
     */
    protected $supportedProviders = [
        'CodePen',
        'DailyMotion',
        'Deviantart',
        'Flickr',
        'GettyImages',
        'Gfycat',
        'Giphy',
        'Imgur',
        'Kickstarter',
        'Livestream',
        'Reddit',
        'Scribd',
        'Sketchfab',
        'Slideshare',
        'SoundCloud',
        'SpeakerDeck',
        'Spotify',
        'Ted',
        'Tumblr',
        'Twitter',
        'Vimeo',
        'Wordpress',
        'Youtube',
    ];

    /**
     * @var string[] A list of providers to never enable
     */
    protected $brokenProviders = [
        'Sketchfab', // Unable to get it working
        'Wordpress', // Need to check 2.0.14 updates
    ];

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
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->app    = empty($config['app']) ? Factory::getApplication() : $config['app'];
        $this->params = empty($config['params']) ? new Registry() : $config['params'];

        $this->registerProvider($this->supportedProviders);
    }

    /**
     * @inheritDoc
     */
    public function registerProvider($names, $prefix = true)
    {
        if (!is_array($names)) {
            $names = [$names];
        }

        // Don't allow the ones we know don't work
        if ($names = array_diff($names, $this->brokenProviders)) {
            parent::registerProvider($names, $prefix);

            // Filter out any URLs this collection doesn't support
            $this->providers = array_filter($this->providers, [$this, 'filterUrls'], ARRAY_FILTER_USE_KEY);
        }
    }

    /**
     * For use by array_filter() with ARRAY_FILTER_USE_KEY flag
     *
     * @param string $url
     *
     * @return bool
     */
    protected function filterUrls($url)
    {
        foreach ($this->excludeUrls as $excludeUrl) {
            if (preg_match('#' . preg_quote($excludeUrl, '#') . '#', $url)) {
                if ($this->params->get('debug')) {
                    $this->app->enqueueMessage(
                        Text::sprintf('PLG_CONTENT_OSEMBED_URL_DISABLED', $url),
                        'notice'
                    );
                }
                return false;
            }
        }

        return true;
    }
}
