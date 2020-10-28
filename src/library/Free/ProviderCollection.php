<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2020 Joomlashack.com. All rights reserved
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

use Embera\ProviderCollection\CustomProviderCollection;

defined('_JEXEC') or die();

class ProviderCollection extends CustomProviderCollection
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->registerProvider([
            'CodePen',
            'DailyMotion',
            'Deviantart',
            'Facebook',
            'Flickr',
            'GettyImages',
            'Gfycat',
            'Giphy',
            'Hulu',
            'Kickstarter',
            'Livestream',
            'Meetup',
            'Reddit',
            'Scribd',
            'Sketchfab',
            'Slideshare',
            'SoundCloud',
            'SpeakerDeck',
            'Spotify',
            'Ted',
            'Twitch',
            'Twitter',
            'Vimeo',
            'Youtube',
        ]);
    }
}
