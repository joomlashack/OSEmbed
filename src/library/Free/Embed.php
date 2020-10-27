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

namespace Alledia\OSEmbed\Free;

defined('_JEXEC') or die();

use Embera\ProviderCollection\SlimProviderCollection;
use Joomla\Registry\Registry;

class Embed
{
    /**
     * @var Embera
     */
    protected $embera = null;

    /**
     * @var Registry
     */
    protected $params = null;

    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    /**
     * @param Registry $params
     *
     * @return Embera
     */
    protected function getEmbera()
    {
        if ($this->embera === null) {
            $config = [
                'responsive'  => true,
                'ignore_tags' => (array)$this->params->get('ignore_tags', ['pre', 'code', 'a', 'img', 'iframe'])
            ];

            $providers = new SlimProviderCollection();

            $this->embera = new Embera($config, $providers, null, $this->params);

            // @TODO: Disable certain options
            //static::$embera->addProvider('youtu.be', '\\Alledia\\OSEmbed\\Free\\Provider\\Example');
        }

        return $this->embera;
    }

    /**
     * @param string $content
     * @param bool   $stripNewLine
     *
     * @return string
     * @throws \Exception
     */
    public function parseContent($content, $stripNewLine = false)
    {
        $embera = $this->getEmbera();
        if ($content && $embera) {
            if ($stripNewLine) {
                return preg_replace('/\n/', '', $embera->autoEmbed($content));
            } else {
                return $embera->autoEmbed($content);
            }
        }

        return $content;
    }

    public function onContentBeforeSave($article)
    {
        return true;
    }
}
