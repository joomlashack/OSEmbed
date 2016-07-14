<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2016 Alledia.com, All rights reserved
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
