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

define([
    'jquery',
    'core/ajax'
], function ($, Ajax) {
    return {

        /**
         * Initialize the module.
         *
         * Called via:
         * $PAGE->requires->js_call_amd(
         *     'local_stablezhelpers/course/general_section',
         *     'setting_hideshow'
         * );
         *
         * @param {Object} data
         */
        setting_hideshow: function (data) {
            window.console.log(data);
            if (data.is_editing) {
                this.add_general_section_setting_hideshow(data);
            } else {
                this.check_general_section_visible(data);
            }
            this.update_general_section_badge(data);

        },

        /**
         * Add Show/Hide menu item to General section action menu.
         *
         * @param {object} data
         */
        add_general_section_setting_hideshow: function (data) {
            var generalSectionActionMenu = $('#section-0 #action-menu-1-menu');

            if (!generalSectionActionMenu.length) {
                return;
            }

            // Remove existing General section Show/Hide item.
            generalSectionActionMenu.find(
                '[data-action="cmStableZShowSection0"], ' +
                '[data-action="cmStableZHideSection0"]'
            ).remove();

            const is_visible = data.is_visible;

            const action = is_visible ?
                'cmStableZHideSection0' :
                'cmStableZShowSection0';


            const iconClass = is_visible ?
                'fa-eye-slash' :
                'fa-eye';

            const text = is_visible ?
                'Hide' :
                'Show';

            // Create menu link.
            const menuLink = $('<a>', {
                href: 'stablez-general-section-action',
                id: 'stablez-setting-showhide-section-0',
                class: 'dropdown-item menu-action cm-edit-action',
                'data-action': action,
                'data-visible': is_visible ? '1' : '0',

            });

            // Create icon.
            const icon = $('<i>', {
                class: 'icon fa-regular ' + iconClass + ' fa-fw',
                'aria-hidden': 'true'
            });

            // Create text.
            const menuText = $('<span>', {
                class: 'menu-action-text',
                text: text
            });

            menuLink.append(icon);
            menuLink.append(menuText);

            // Add to menu.
            generalSectionActionMenu.append(menuLink);

            // Click event.
            menuLink.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                var visible = $(this).data('visible') === 1;

                // return '';
                const request = {
                    methodname: 'local_stablezhelpers_course_general_section_setting',
                    args: {
                        courseid: data.courseid,
                        visible: !visible
                    }
                };
                Ajax.call([request])[0]
                    .done(function (response) {
                        if (response.success) {
                            menuLink.attr(
                                'data-visible',
                                response.visible ? '1' : '0'
                            );
                        } else {
                            window.console.log('error to save.');
                        }
                    }).fail(function () {
                        window.console.log('request failed.');
                    }).always(function () {
                        window.location.reload();
                    }
                    );
            });
        },

        /**
         * Check general section visible
         *
         * @param {*} data
         */
        check_general_section_visible: function (data) {
            if (!data.is_visible && (data.is_student || data.norole)) {
                // $('#section-0').hide();
                $('#section-0 a[role="button"][data-for="sectiontoggler"]').remove();
                $('#section-0 .sectionname').remove();
                $('#section-0 .sectionbadges').remove();
                $('#section-0 .course-content-item-content[id^="coursecontentcollapseid"]').remove();
                $('#section-0 #section-item').remove();
            }
        },

        /**
         * Update General section "Hidden from students" badge.
         *
         * @param {object} data
         */
        update_general_section_badge: function (data) {
            var sectionBadges = $(
                '#section-0 .course-section-header .sectionbadges'
            );

            if (!sectionBadges.length) {
                return;
            }

            // Remove only our General section badge.
            sectionBadges.find(
                '[data-general-section-hidden-badge-stablez-info]'
            ).remove();

            // If visible, no hidden badge is required.
            if (data.is_visible) {
                return;
            }

            var badge = $('<span>', {
                class: 'badge rounded-pill bg-secondary text-dark order-2',
                'data-general-section-hidden-badge-stablez-info': '1'
            });

            var icon = $('<i>', {
                class: 'icon fa-regular fa-eye-slash fa-fw',
                'aria-hidden': 'true'
            });

            badge.append(icon);
            badge.append('Hidden from students');

            sectionBadges.append(badge);
        }
    };
});