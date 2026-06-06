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
 * AMD module for Stablez block configuration form behaviour.
 *
 * Responsibilities:
 *  1. Show/hide the course-list field based on the selected block type.
 *  2. Mark config_course_fields_layout as required and validate before submit.
 *  3. Track and persist the user-defined selection order for config_course_fields_order.
 *  4. Track and persist the user-defined selection order for config_course_list_order.
 *
 * Usage (PHP):
 *   $PAGE->requires->js_call_amd('block_stablezblocks/edit-form', 'init');
 *
 * @module     block_stablezblocks/edit-form
 * @copyright  2024 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/**
* Persists the current selection order to the hidden input and re-sorts the
* visible autocomplete <span> tags to match that order.
*
* A short setTimeout is used to allow Moodle's autocomplete widget to finish
* its own DOM updates before we reorder the rendered spans.
*
* @param {string[]}    selectedOrder   Ordered array of selected data-value strings.
* @param {HTMLInputElement} hiddenInput The hidden input that stores the order value.
* @param {HTMLElement} containerDiv    The fitem wrapper div for the autocomplete field.
*/
function rearrangeOptionSpanDisplay(selectedOrder, hiddenInput, containerDiv) {
    if (selectedOrder.length > 0) {
        hiddenInput.value = selectedOrder.join(',');
        // Defer reordering to let Moodle's autocomplete widget finish rendering
        // any newly added or removed span elements before we sort them.

        let elapsed = 0;
        const intervalRearrange = setInterval(function () {
            elapsed += 100;

            const selectionContainer = containerDiv.querySelector('.form-autocomplete-selection');

            if (selectionContainer) {
                // Sort the rendered span elements according to selectedOrder.
                const sortedSpans = Array.from(selectionContainer.children).sort((a, b) => {
                    const aIndex = selectedOrder.indexOf(a.getAttribute('data-value'));
                    const bIndex = selectedOrder.indexOf(b.getAttribute('data-value'));
                    return aIndex - bIndex;
                });

                // Re-append in sorted order.
                sortedSpans.forEach(span => selectionContainer.appendChild(span));

                clearInterval(intervalRearrange);
                return;
            }

            // Stop after 600ms.
            if (elapsed >= 600) {
                clearInterval(intervalRearrange);
            }
        }, 100);

    } else {
        hiddenInput.value = '';
    }
}

/**
 * SHOW / HIDE COURSE LIST FIELD BASED ON BLOCK TYPE
 */
function show_hide_course_list_field() {
    /**
     * The block-type <select> element.
     * @type {HTMLSelectElement}
     */
    const configBlockType = document.querySelector('select[id^="id_config_stablez_block_type"]');

    /**
     * The wrapper div for the course-list field.
     * @type {HTMLElement}
     */
    const configCourselistWrapper = document.querySelector('div[id^="fitem_id_config_course_list"]');

    /**
     * Block types that require the course-list field to be visible.
     * @type {string[]}
     */
    const BLOCK_TYPES_WITH_COURSELIST = ['course_list', 'multi_course_info'];

    /**
     * Toggles the visibility of the course-list field based on the current block type value.
     */
    function toggleCourselistVisibility() {
        const isVisible = BLOCK_TYPES_WITH_COURSELIST.includes(configBlockType.value);
        configCourselistWrapper.style.display = isVisible ? '' : 'none';
    }

    // Set initial visibility on page load.
    toggleCourselistVisibility();

    // Update visibility whenever the block type changes.
    configBlockType.addEventListener('change', toggleCourselistVisibility);
}

/**
 * Manage Course information fields
 */
function manage_course_information_fields() {
    $('select[name="config_course_fields"]').
        wrap(
            '<div class="config_course_fields_wrapper d-flex flex-wrap align-items-start flex-column" style="gap:12px;"></div>'
        );
    let radios = `
            <div class="course-field-return-options d-flex align-items-center gap-2" style="gap:12px;">
                <label class="m-0">
                    <input type="radio" name="course_field_display" value="label-value">
                    Show Label & Value
                </label>
                <label class="m-0">
                    <input type="radio" name="course_field_display" value="value" checked>
                    Show Only Value
                </label>
            </div>
            <button type="button" id="add-course-field-option" class="btn btn-primary ">
                Add course field
            </button>
        `;
    $('.config_course_fields_wrapper').append(radios);
    $('.config_course_fields_wrapper').prepend('<div class="selected-course-field-wrapper"><ul></ul></div>');
    const $course_fields_order = $('input[name="config_course_fields_order"]');
    let course_fields = {};

    // load existing data
    if ($course_fields_order.val()) {
        try {
            course_fields = JSON.parse($course_fields_order.val());
        } catch (e) {
            course_fields = {};
        }
        // render $course_fields_order.val() items
        $.each(course_fields, function (key, value) {

            let selectText = $('select[name="config_course_fields"] option[value="' + key + '"]').text();
            let checkedText = $('input[name="course_field_display"][value="' + value + '"]').closest('label').text().trim();

            $('.selected-course-field-wrapper').append(`
            <li data-key="${key}">
                ${checkedText} for field "${selectText}"
                <span class="remove-course-field" title="Remove" style="cursor:pointer; margin-left:8px; color:red;">✖</span>
            </li>
        `);
        });
    }

    //
    $('#add-course-field-option').on('click', function () {
        let $select = $('select[name="config_course_fields"]');
        let config_course_fields_value = $select.val();
        let config_course_fields_label = $select.find('option:selected').text();

        if (!config_course_fields_value) {
            return;
        }

        if (course_fields.hasOwnProperty(config_course_fields_value)) {
            return;
        }

        let $checked = $('input[name="course_field_display"]:checked');
        let course_field_display_value = $checked.val();
        let course_field_display_label = $checked.closest('label').text().trim();

        $select.val('').trigger('change');

        // store only if not exists
        course_fields[config_course_fields_value] = course_field_display_value;

        $('.selected-course-field-wrapper').append(`
            <li data-key="${config_course_fields_value}">
                ${course_field_display_label} for field "${config_course_fields_label}"
            <span class="remove-course-field" title="Remove" style="cursor:pointer; margin-left:8px; color:red;">✖</span>
            </li>
        `);
        $course_fields_order.val(JSON.stringify(course_fields));
    });

    $(document).on('click', '.remove-course-field', function () {
        let $li = $(this).closest('li');
        let key = $li.data('key');
        // remove from object
        if (course_fields.hasOwnProperty(key)) {
            delete course_fields[key];
        }
        // remove UI
        $li.remove();
        // update hidden input
        $course_fields_order.val(JSON.stringify(course_fields));
    });
}

/**
 *
 */
function manage_config_course_list_order() {
    /**
     * Hidden input that stores the user-defined order of the course list as a
     * comma-separated string.
     * @type {HTMLInputElement}
     */
    const courselistOrderInput = document.querySelector("input[name='config_course_list_order']");
    const courselistContainer = document.querySelector("div[id^='fitem_id_config_course_list_']");

    /**
     * Tracks the current ordered list of selected course-list values.
     * Kept separate from courseFieldsSelectedOrder to avoid shared-state bugs.
     * @type {string[]}
     */
    let courselistSelectedOrder = courselistOrderInput.value
        ? courselistOrderInput.value.split(',')
        : [];

    // On page load, re-apply the saved order to the autocomplete UI.
    if (courselistSelectedOrder.length > 0) {
        rearrangeOptionSpanDisplay(courselistSelectedOrder, courselistOrderInput, courselistContainer);
    }

    $(document).on('change', 'select[name="config_course_list[]"]', function () {
        let val = $(this).val() || []; // Current selected values

        // Add newly selected items to the end
        val.forEach(function (item) {
            if (!courselistSelectedOrder.includes(item)) {
                courselistSelectedOrder.push(item);
            }
        });

        // Remove unselected items
        courselistSelectedOrder = courselistSelectedOrder.filter(function (item) {
            return val.includes(item);
        });

        rearrangeOptionSpanDisplay(courselistSelectedOrder, courselistOrderInput, courselistContainer);
    });
}

/**
 *
 */
export const init = () => {
    show_hide_course_list_field();
    manage_course_information_fields();
    manage_config_course_list_order();
};