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
 * @copyright 2025 santoshmagar.com.np
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
import $ from 'jquery';
/* global CodeMirror */

/**
 *
 * @param {*} theme_name
 * @param {*} data
 */
export const init = (theme_name, data = {}) => {
    if ($('body').hasClass('pagelayout-admin')) {

        theme_setting_tab_panel_title_toggle(theme_name);
        define_number_fields(theme_name, data.number_fields);
        initCodeMirror('id_s_theme_' + theme_name + '_scss', 'css');
        initCodeMirror('id_s_theme_' + theme_name + '_custom_js', 'javascript');

    }
};

/**
 *
 * @param {*} theme_name
 */
function theme_setting_tab_panel_title_toggle(theme_name) {
    let currentUrl = window.location.href;
    let url = new URL(currentUrl);
    let params = new URLSearchParams(url.search);
    let query_title_id = params.get('tabtitle');
    // let section = params.get('section');
    let hash = url.hash;

    const stored = localStorage.getItem('theme_stablez_form_state');
    if (stored) {
        const data = JSON.parse(stored);
        // Check expiry.
        if (Date.now() < data.expiry) {
            // section = data.section;
            query_title_id = data.query_title_id;
            hash = data.hash;
        } else {
            // Remove expired data.
            localStorage.removeItem('theme_stablez_form_state');
        }
    }

    $('#page-admin-setting-themesetting' + theme_name + ' #adminsettings ul.nav-tabs li.nav-item a').on('click', function () {
        params.delete('tabtitle');
        hash = $(this).attr('href');
        currentUrl = url.pathname + '?' + params.toString() + hash;
        window.history.replaceState('', '', currentUrl);
    });

    $('#page-admin-setting-themesetting' + theme_name + ' #adminsettings .tab-content .tab-pane').each(function () {
        var h3_title = $(this).find('h3.main');
        if (h3_title) {
            let query_title_id_true = false;
            h3_title.addClass('title-tab-pane-toggle');
            //
            h3_title.on('click', function () {
                $(this).toggleClass("open");
                $(this).next().toggleClass("open");
                // Change url to add query parameter
                if ($(this).hasClass("open")) {
                    params.set('tabtitle', $(this).attr('id'));
                } else {
                    params.delete('tabtitle');
                }
                currentUrl = url.pathname + '?' + params.toString() + hash;
                window.history.replaceState('', '', currentUrl);
            });
            h3_title.each(function () {
                $(this).nextUntil('h3.main').wrapAll('<div class="title-tab-pane-content">');
                let h3_title_id = 'theme-' + theme_name + '-' + ($(this).text().toLowerCase()).replace(/\s+/g, '-');
                $(this).attr('id', h3_title_id);
                if (query_title_id == h3_title_id) {
                    query_title_id_true = true;
                }
            });
            // Add 'open' class to the first h3.main in each tab-pane
            if (!query_title_id_true) {
                h3_title.first().addClass('open');
                h3_title.first().next().addClass('open');
            } else {
                $('#' + query_title_id).addClass('open');
                $('#' + query_title_id).next().addClass('open');
                //
                setTimeout(function () {
                    const offset = -100;
                    const element = document.getElementById(query_title_id);
                    if (element) {
                        const elementPosition = element.getBoundingClientRect().top + window.scrollY;
                        window.scrollTo({ top: elementPosition + offset, behavior: "smooth" });
                    }

                }, 1000);
            }
        }
    });

    $('form#adminsettings button[type="submit"]').on('click', function () {
        let currentUrl = window.location.href;
        let url = new URL(currentUrl);
        let params = new URLSearchParams(url.search);
        let hash = url.hash;
        var query_title_id = params.get('tabtitle');
        let section = params.get('section');

        // Store data.
        const formState = {
            hash: hash,
            query_title_id: query_title_id,
            section: section,
            expiry: Date.now() + (30 * 1000) // 30 seconds.
        };

        localStorage.setItem(
            'theme_stablez_form_state',
            JSON.stringify(formState)
        );

        $('form#adminsettings').attr('action', currentUrl);
    });

}

/**
 *
 * @param {*} theme_name
 * @param {*} number_fields
 */
function define_number_fields(theme_name, number_fields) {
    // Edit input fields with id to number field
    $.each(number_fields, function (index, value) {
        if ($('input#id_s_theme_' + theme_name + '_' + value)) {
            $('input#id_s_theme_' + theme_name + '_' + value).attr('type', 'number');
            $('input#id_s_theme_' + theme_name + '_' + value).attr('min', 0);
        }
    });
}

/**
 *
 * @param {*} selectorId
 * @param {*} mode
 */
function initCodeMirror(selectorId, mode) {
    const textarea = document.getElementById(selectorId);
    if (textarea && typeof CodeMirror !== 'undefined') {
        CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
            mode: mode,
            theme: 'material-palenight',
            autoRefresh: true
        });
    }
}

