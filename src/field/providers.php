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

use Alledia\OSEmbed\Free\Helper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die();

class OsembedFormFieldProviders extends FormField
{
    protected $layout            = null;
    protected $renderLabelLayout = null;

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return       = parent::setup($element, $value, $group);
        $this->hidden = true;

        return $return;
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        $dispatcher   = JEventDispatcher::getInstance();
        $providers    = [];
        $excludeHosts = [];

        $providerLists = $dispatcher->trigger('onOsembedProviders');
        foreach ($providerLists as $providerList) {
            if (isset($providerList->providers)) {
                $providers = array_merge($providerList->providers);
            }
            if (isset($providerList->excludeHosts)) {
                $excludeHosts = array_merge($excludeHosts, $providerList->excludeHosts);
            }
        }

        $providerNames = [];
        foreach ($providers as $host => $provider) {
            $providerParts = explode('\\', $provider);
            $providerName  = array_pop($providerParts);

            if (!isset($providerNames[$providerName])) {
                $providerNames[$providerName] = [];
            }
            if (!in_array($host, $excludeHosts)) {
                $providerNames[$providerName][] = $host;
            }
        }

        if ($providerNames) {
            return $this->displayProviders($providerNames);
        }

        Log::add(Text::_('PLG_CONTENT_OSEMBED_ERROR_NO_PROVIDERS_LOG'), Log::WARNING, Helper::LOG_SYSTEM);

        return sprintf(
            '<span class="alert alert-error">%s</span>',
            Text::_('PLG_CONTENT_OSEMBED_ERROR_NO_PROVIDERS')
        );
    }

    /**
     * @param string[][] $providerNames
     *
     * @return string
     */
    protected function displayProviders(array $providerNames)
    {
        $html = [
            '<table class="table table-striped">',
            '<thead>',
            '<tr>',
            '<th>Provider</th>',
            '<th>Host Names</th>',
            '</tr>',
            '</thead>',
            '<tbody>'
        ];

        $row = 0;
        foreach ($providerNames as $providerName => $hosts) {
            $html[] = sprintf(
                '<tr class="%s"><td width="5%%">%s</td><td>%s</td></tr>',
                'row' . $row++,
                $providerName,
                join('<br>', $hosts)
            );
        }

        $html[] = '</tbody></table>';

        return join("\n", $html);
    }

    /**
     * @inheritDoc
     */
    protected function getLayoutData()
    {
        return [];
    }
}
