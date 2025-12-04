/**
 * @package   OSEmbed
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2026 Joomlashack.com. All rights reserved
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

;jQuery(document).ready(function($) {
    let $providers = $([
        '.embera-embed-responsive-provider-flickr'
    ].join(','));

    $providers.find('iframe:not(width,height)').each(function() {
        let $this  = $(this),
            src    = $this.attr('src'),
            width  = src.match(/width=(\d+)/).pop(),
            height = src.match(/height=(\d+)/).pop();

        $this.attr({
            width : width,
            height: height
        })
    });

    let reset = null;
    $(window).on('load resize', function() {
        if (reset) {
            clearTimeout(reset)
        }

        reset = setTimeout(function() {
                $providers.each(function() {
                    let oldHeight = $(this).find('iframe').attr('height'),
                        oldWidth  = $(this).find('iframe').attr('width');

                    if (oldHeight && oldWidth) {
                        let newWidth  = $(this).width(),
                            newHeight = (oldHeight / oldWidth) * newWidth;

                        $(this).find('iframe').css({
                            height: newHeight + 'px',
                            width : newWidth + 'px'
                        });
                    }
                });
            },
            350
        );
    });
});
