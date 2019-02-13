/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2019 Joomlashack.com. All rights reserved
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
