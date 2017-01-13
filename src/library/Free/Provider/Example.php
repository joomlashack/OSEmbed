<?php
/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
