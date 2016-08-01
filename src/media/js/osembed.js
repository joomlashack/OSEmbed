/**
 * @package   OSEmbed
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// Original code by arigesher from https://gist.github.com/arigesher/8932051

(function($)
{
    $(document).ready(function() {
        $('.ose-flickr iframe').each(function(index) {
            var ratio = $(this).height() / $(this).width();
            var origHeight = $(this).height();
            var origWidth  = $(this).width();
            var self = this;
            $(window).resize(function() {
                if($(self).parent().width() > origWidth) {
                    $(self).width(origWidth);
                    $(self).height(origHeight);
                } else {
                    $(self).width($(self).parent().width());
                    $(self).height($(self).parent().width() * ratio);
                }
            });
        });
        $(window).resize();
    });
});