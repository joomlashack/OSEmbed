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

defined('_JEXEC') or die;

use Alledia\Framework\Joomla\Extension;
use Alledia\OSEmbed\Free\Embed;
use Alledia\OSEmbed\Free\Helper;
use Joomla\Event\Dispatcher;
use Joomla\Registry\Registry;

include_once 'include.php';

if (defined('OSEMBED_LOADED')) {
    /**
     * OSEmbed Content Plugin
     */
    class PlgContentOSEmbed extends Extension\AbstractPlugin
    {
        protected $namespace = 'OSEmbed';

        /**
         * @var string
         */
        public $type = 'content';

        /**
         * @var bool
         */
        protected $allowedToRun = true;

        /**
         * PlgContentOSEmbed constructor.
         *
         * @param Dispatcher $subject
         * @param array      $config
         *
         * @return void
         * @throws Exception
         */
        public function __construct(&$subject, $config = array())
        {
            parent::__construct($subject, $config);

            $option  = JFactory::getApplication()->input->get('option');
            $docType = JFactory::getDocument()->getType();

            // Do not run if called from OSMap's XML view
            if ($option === 'com_osmap' && $docType !== 'html') {
                $this->allowedToRun = false;
            }

            if ($this->allowedToRun) {
                $this->init();

                // Check the minumum requirements
                $helperClass = $this->getHelperClass();
                if (!$helperClass::complyBasicRequirements()) {
                    $this->allowedToRun = false;
                }
            }
        }

        /**
         * @return Helper|string
         */
        protected function getHelperClass()
        {
            if ($this->isPro()) {
                return 'Alledia\\OSEmbed\\Pro\\Helper';
            }

            return 'Alledia\\OSEmbed\\Free\\Helper';
        }

        /**
         * @return Embed|string
         */
        protected function getEmbedClass()
        {
            if ($this->isPro()) {
                return 'Alledia\\OSEmbed\\Pro\\Embed';
            }

            return 'Alledia\\OSEmbed\\Free\\Embed';
        }

        /**
         * Plugin that loads module positions within content
         *
         * @param string   $context
         * @param object   $article
         * @param Registry $params
         * @param int      $page
         *
         * @return  void
         * @throws Exception
         */
        public function onContentPrepare($context, $article, $params, $page = 0)
        {
            // Don't run this plugin when the content is being indexed
            if ($context == 'com_finder.indexer' || !$this->allowedToRun) {
                return;
            }

            $versionUID = md5($this->extension->getVersion());

            JHtml::_('jquery.framework');

            JHtml::_(
                'stylesheet',
                'plg_content_osembed/osembed.css',
                array('relative' => true, 'version' => $versionUID)
            );

            JHtml::_(
                'script',
                'plg_content_osembed/osembed.js',
                array('relative' => true, 'version' => $versionUID)
            );

            $embedClass    = $this->getEmbedClass();
            $article->text = $embedClass::parseContent($article->text, false);
        }

        public function onContentBeforeSave($context, $article, $isNew)
        {
            $embedClass = $this->getEmbedClass();

            return $embedClass::onContentBeforeSave($article);
        }
    }
}
