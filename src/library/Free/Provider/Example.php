<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSEmbed\Free\Provider;

defined('_JEXEC') or die();

use Embera;
use Embera\Adapters\Service;

class Example extends Service
{
    /** inline {@inheritdoc} */
    protected $apiUrl = 'http://www.example.com';

    /** inline {@inheritdoc} */
    protected function validateUrl()
    {
        return false;
    }
}
