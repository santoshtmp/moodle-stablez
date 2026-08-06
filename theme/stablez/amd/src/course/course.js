// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AMD module
 *
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://santoshmagar.com.np//
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define(['jquery'], function ($) {
    return {
        /**
        * Initialize the module.
        * Called via $PAGE->requires->js_call_amd('theme_mytheme/collapse_sections', 'init').
        */
        coursecollapsetopic: function () {
            /**
            * Auto-collapse all sections on page load.
            *
            * Polls every 100ms for the #collapsesections button.
            * Once found and aria-expanded="true" (meaning sections are expanded),
            * fires a native DOM click to collapse all, then clears the interval.
            */
            var intervalCollapsesections = setInterval(function () {
                var $collapsesections = $('a[id="collapsesections"]');
                if ($collapsesections.length) {
                    var ariaExpanded = $collapsesections.attr('aria-expanded');
                    var ariaControls = $collapsesections.attr('aria-controls');
                    // Only click if sections are expanded and aria-controls is set
                    // (aria-controls being set means Moodle has fully initialized the toggle)
                    if (ariaExpanded === 'true' && ariaControls) {
                        clearInterval(intervalCollapsesections);
                        $collapsesections[0].click();
                    }
                }
            }, 100);

            /**
             * Safety timeout: force-clear the interval after 1.5 minutes
             * in case #collapsesections never appears (e.g. no sections, different format).
             */
            setTimeout(function () {
                clearInterval(intervalCollapsesections);
                window.console.log('Interval cleared by safety timeout (1.5 min)');
            }, 90000); // 90000ms = 1.5 minutes
        },

        /**
        * section title link clicks.
        */
        sectiontitleclick: function () {
            /**
            * Intercept section title link clicks.
            *
            * Moodle renders section titles as <a> tags inside .sectionname,
            * but the actual collapse toggle is a separate <a> with id="collapsesectionidX".
            * This listener:
            *  - Prevents the default href action
            *  - Stops event bubbling and other listeners (stopImmediatePropagation)
            *  - Finds the correct collapse toggle inside the section header
            *  - Fires a native click on it instead
            *
            * Uses event delegation on $(document) so it works for dynamically
            * rendered sections that may not exist at init time.
            */
            $('.section-item .sectionname > a').on('click', function (e) {
                e.preventDefault();          // prevent following the href
                e.stopPropagation();         // stop event bubbling up the DOM
                e.stopImmediatePropagation(); // stop other listeners on this same element


                var $header = $(this).closest('.course-section-header');
                var $toggleBtn = $header.find('a[id^="collapsesectionid"]');

                if ($toggleBtn.length) {
                    $toggleBtn[0].click();
                }
            });
        },

        /**
         * For course category page
         */
        coursecategory: function () {
            // Expand all course category
            const $collapseexpand = $(document).find('#region-main .collapsible-actions a.collapseexpand');
            if ($collapseexpand.length && !$collapseexpand.hasClass('collapse-all')) {
                $collapseexpand[0].click();
            }
        },

        /**
        * Course field video
        */
        coursefield_remotevideo: function () {
            const $fieldvideo = $(document).find('.remote-video-field');
            if ($fieldvideo.length) {
                $fieldvideo.each(async function () {
                    const video = $(this).data('remote-video');

                    let videoType = '';
                    let videoId = '';
                    let videoUrl = video;
                    let thumbnailUrl = '';

                    if (video.includes('vimeo.com')) {
                        videoType = 'vimeo';
                    } else if (video.includes('youtube.com') || video.includes('youtu.be')) {
                        videoType = 'youtube';
                    }

                    if (videoType === 'youtube') {
                        const pattern = new RegExp(
                            '(?:https?:\\/\\/)?(?:www\\.)?' +
                            '(?:youtube\\.com\\/' +
                            '(?:[^/\\n\\s]+\\/\\S+\\/|(?:v|e(?:mbed)?)\\/|\\S*\\?v=)|' +
                            'youtu\\.be\\/)' +
                            '([a-zA-Z0-9_-]{11})'
                        );

                        const matches = video.match(pattern);

                        if (matches) {
                            videoId = matches[1];
                            thumbnailUrl = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
                            videoUrl = `https://www.youtube.com/embed/${videoId}`;
                        }
                    }

                    if (videoType === 'vimeo') {
                        const pattern = new RegExp(
                            'vimeo\\.com\\/' +
                            '(?:channels\\/[\\w]+\\/|' +
                            'groups\\/[\\w]+\\/videos\\/|' +
                            'album\\/\\d+\\/video\\/|' +
                            'video\\/|)' +
                            '(\\d+)' +
                            '(?:\\?.*?h=([\\w\\d]+))?'
                        );
                        const matches = video.match(pattern);

                        if (matches) {
                            videoId = matches[1];

                            if (matches[2]) {
                                videoId += '/' + matches[2];
                            }

                            videoUrl = `https://player.vimeo.com/video/${matches[1]}`;

                            // Fetch thumbnail from Vimeo oEmbed API
                            try {
                                const response = await fetch(
                                    `https://vimeo.com/api/oembed.json?url=${encodeURIComponent(video)}`
                                );
                                const data = await response.json();
                                thumbnailUrl = data.thumbnail_url || '';
                            } catch (e) {
                                window.console.error('Unable to fetch Vimeo thumbnail', e);
                            }
                        }
                    }

                    $(this).attr('data-video-type', videoType);
                    $(this).attr('data-video-id', videoId);
                    $(this).attr('data-thumbnail-url', thumbnailUrl);
                    $(this).append(`
                        <iframe
                            src="${videoUrl}"
                            frameborder="0"
                            allowfullscreen>
                        </iframe>
                    `);
                });
            }
        }
    };
});