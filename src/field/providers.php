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

use Alledia\OSEmbed\Free\Helper;
use Embera\Embera;
use Joomla\CMS\Factory;
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
        if ($return = parent::setup($element, $value, $group)) {
            if (!defined('OSEMBED_LOADED')) {
                $path = JPATH_PLUGINS . '/content/osembed/include.php';
                if ($return = is_file($path)) {
                    require_once $path;
                }
            }
        }
        $this->hidden = true;

        return $return;
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        $providerLists = Factory::getApplication()->triggerEvent('onOsembedProviders');

        $providerNames = [];
        foreach ($providerLists as $providers) {
            foreach ($providers as $host => $provider) {
                $providerParts = explode('\\', $provider);
                $providerName  = array_pop($providerParts);

                if (!isset($providerNames[$providerName])) {
                    $providerNames[$providerName] = [];
                }
                $providerNames[$providerName][] = $host;
            }
        }

        if ($providerNames) {
            $hostCount = Text::plural(
                'PLG_CONTENT_OSEMBED_PROVIDER_HOST_COUNT',
                count($providerNames, COUNT_RECURSIVE) - count($providerNames)
            );

            $header = sprintf(
                '<div class="alert alert-info">%s<br>%s</div>',
                Text::plural('PLG_CONTENT_OSEMBED_PROVIDER_COUNT', count($providerNames), $hostCount),
                Text::sprintf('PLG_CONTENT_OSEMBED_EMBERA_VERSION', Embera::VERSION)
            );

            return $header . $this->displayProviders($providerNames);
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
        ksort($providerNames, SORT_NATURAL | SORT_FLAG_CASE);

        $tableStart = [
            '<div class="span6">',
            '<table class="table table-striped" style="border: 1px solid #ddd">',
            '<thead>',
            '<tr>',
            sprintf('<th>%s</th>', Text::_('PLG_CONTENT_OSEMBED_PROVIDER')),
            sprintf('<th>%s</th>', Text::_('PLG_CONTENT_OSEMBED_PROVIDER_HOSTS')),
            '</tr>',
            '</thead>',
            '<tbody>'
        ];

        $tableEnd = [
            '</tbody>',
            '</table>',
            '</div>'
        ];

        $html    = [];
        $columns = $this->createColumns($providerNames);
        foreach ($columns as $providerNames) {
            $row  = 0;
            $html = array_merge($html, $tableStart);
            foreach ($providerNames as $providerName => $hosts) {
                $html[] = sprintf(
                    '<tr class="%s"><td style="width: 5%%">%s</td><td>%s</td></tr>',
                    'row' . $row++,
                    $providerName,
                    join('<br>', $hosts)
                );
            }
            $html = array_merge($html, $tableEnd);
        }

        return sprintf('<div class="row-fluid">%s</div>', join("\n", $html));
    }

    /**
     * @inheritDoc
     */
    protected function getLayoutData()
    {
        return [];
    }

    /**
     * Split $list into two roughly even columns accounting
     * for the number of items in each element of $list
     *
     * @param string[][] $list
     *
     * @return string[][][]
     */
    protected function createColumns(array $list)
    {
        $columns = array_chunk($list, ceil(count($list) / 2), true);

        $countDiff = (int)((($this->getHostCount($columns[0]) - $this->getHostCount($columns[1])) / 2) / 2);

        if ($countDiff && $countDiff < (count($list) / 4)) {
            // Even out the columns only if it's a relatively minor difference
            if ($countDiff > 0) {
                // Move end of column 1 to top of column 2
                $move = array_slice($columns[0], -$countDiff, null, true);

                $columns[0] = array_slice($columns[0], 0, count($columns[0]) - $countDiff, true);
                $columns[1] = array_merge($move, $columns[1]);

            } elseif ($countDiff < 0) {
                // Move top of column 2 to bottom of column 1
                $countDiff  = abs($countDiff);
                $move       = array_slice($columns[1], 0, $countDiff, true);
                $columns[1] = array_slice($columns[1], $countDiff, null, true);
                $columns[0] = array_merge($columns[0], $move);
            }
        }

        return $columns;
    }

    /**
     * @param string[][] $providers
     *
     * @return int
     */
    protected function getHostCount(array $providers)
    {
        return (int)array_sum(
            array_map(
                function ($hosts) {
                    return count($hosts);
                },
                $providers
            )
        );
    }
}
