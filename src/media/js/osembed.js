/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function($)
{
    $(window).resize(function() {
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

            // Only for Facebook
            /*if( $(this).is('.ose-facebook') ) {
             console.log('Facebook');

             $(this).find('.fb_iframe_widget, .fb_iframe_widget > span').css({
             "height" : $newHeight + "px",
             "width" : $newWidth + "px"
             });

             console.log( $(this).find('iframe').attr('name') );
             }*/
        });
    });
})(jQuery);
