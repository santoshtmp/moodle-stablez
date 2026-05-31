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
            * PART 1: Auto-collapse all sections on page load.
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


            /**
            * PART 2: Intercept section title link clicks.
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
        }
    };
});