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


class Facebook extends Service
{
    protected $apiUrl = array(
        'https://www.facebook.com/plugins/video/oembed.json',
        'https://www.facebook.com/plugins/post/oembed.json'
    );

    public function validateUrl()
    {
        return preg_match('~http[s]?:\/\/(?:www\.)?facebook\.com\/(?:[^\/]+\/(?:posts|activity)\/[a-z0-9\-\_]+|(?:photo[s]?|permalink|media|questions|notes|video)(?:\.php)?[\/\?a-z0-9=_\-%&]+|[a-z0-9=_\-]+\/videos\/[0-9a-z]+(?:\/\?[a-z0-9=\-_%&]*)*|[a-z0-9_\-\.]+\/(?:media_set|photos)[0-9a-z\/\.\?=%&_\-]+)[\/]?~i', $this->url);
    }

    public function getApiUrlBaseOnUrl()
    {
        $patterns = array(
            '~http[s]?:\/\/(?:www\.)?facebook\.com\/(?:video\.php\?(?:id|v)=[a-z0-9]+|[a-z0-9\._\-]+\/videos\/[0-9a-z]+[\/]?(?:\?[a-z0-9&%_\-\/\-=]*)?)~i',
            '~http[s]?:\/\/(?:www\.)?facebook\.com\/(?:(?:photo\.php|photo[s]?|permalink\.php|media|questions|notes)[\/\?][a-z0-9=\?&%_\_\.\/\-]+|[a-z0-9_\-\.]+\/(?!videos\/)[a-z0-9=\?&%_\_\.\/\-]+)~i'
        );

        foreach ($patterns as $index => $pattern) {
            if (preg_match($pattern, (string) $this->url)) {
                return $this->apiUrl[$index];
            }
        }

        return false;
    }

    /**
     * Gets the information from an Oembed provider
     * when this fails, it tries to provide a fakeResponse.
     * Returns an associative array with a (common) Oembed response.
     *
     * @return array
     */
    public function getInfo()
    {
        try {
            $apiUrl = $this->getApiUrlBaseOnUrl();

            if (empty($apiUrl)) {
                return array();
            }

            if ($res = $this->oembed->getResourceInfo($this->config['oembed'], $apiUrl, (string) $this->url, $this->config['params'])) {
                return $this->modifyResponse($res);
            }

        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        /**
         * Use fakeResponses when the oembed setting is null or false
         * If the oembed config is true, the user strictly wants real responses
         */
        if (!$this->config['oembed'] && $response = $this->fakeResponse()) {
            $fakeResponse = new \Embera\FakeResponse($this->config, $response);
            return $this->modifyResponse($fakeResponse->buildResponse());
        }

        return array();
    }
}
