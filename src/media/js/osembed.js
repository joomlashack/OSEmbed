/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Joomlashack, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

(function($)
{
    $(window).on('load resize', function() {
        $('.ose-flickr, .ose-facebook').each(function() {

            // Calculate old and new width/height values
            var $oldHeight  =  $(this).find('iframe').attr('height'); // Get iframe's height
            var $oldWidth   =  $(this).find('iframe').attr('width'); // Get iframe's width
            var $newWidth   =  $(this).width(); // Get wrapper's width
            var $newHeight  = ($oldHeight/$oldWidth) * $newWidth;

            // Apply new width/height values
            $(this).find('iframe').css({
                "height" : $newHeight + "px",
                "width" : $newWidth + "px"
            });
        });
    });
})(jQuery);
