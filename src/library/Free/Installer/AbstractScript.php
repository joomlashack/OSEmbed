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

namespace Alledia\OSEmbed\Free\Installer;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

$includePath = realpath(__DIR__ . '/../../../library/Installer/include.php');
if ($includePath) {
    require_once $includePath;

} else {
    require_once __DIR__ . '/AbstractFail.php';
}

/**
 * Class AbstractScript
 *
 * @package Alledia\OSEmbed\Free\Installer
 */
class AbstractScript extends \Alledia\Installer\AbstractScript
{
}
