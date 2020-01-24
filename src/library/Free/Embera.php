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

use Embera\Adapters\Service;
use ReflectionException;

defined('_JEXEC') or die();

class Embera extends \Embera\Embera
{
    /**
     * @param string $body
     *
     * @return array
     * @throws ReflectionException
     */
    public function getUrlInfo($body = null)
    {
        $results = array();
        if ($providers = $this->getProviders($body)) {
            /** @var Service $service */
            foreach ($providers as $url => $service) {
                $serviceInfo = $service->getInfo();

                if (!isset($serviceInfo['provider_name'])) {
                    $reflect = new \ReflectionClass($service);

                    $serviceInfo['provider_name'] = $reflect->getShortName();
                    unset($reflect);
                }

                if (!isset($serviceInfo['provider_alias'])) {
                    $alias = preg_replace('/[^a-z0-9\-]/i', '-', $serviceInfo['provider_name']);
                    $alias = strtolower(str_replace('--', '-', $alias));

                    $serviceInfo['provider_alias'] = $alias;
                }

                if (!isset($serviceInfo['wrapper_class'])) {
                    $serviceInfo['wrapper_class'] = '';
                }

                $results[$url] = $serviceInfo;
                $this->errors  = array_merge($this->errors, $service->getErrors());
            }
        }

        return array_filter($results);
    }
}
