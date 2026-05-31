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
 * AMD module for Stablez block front end.
 *
 * Usage (PHP):
 *   $PAGE->requires->js_call_amd('block_stablezblocks/block-stablez', 'init');
 *
 * @module     block_stablezblocks/block-stablez
 * @copyright  2024 Your Name <you@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

export const init = (blockID) => {
    $('.block_stablezblocks#inst' + blockID).each(function () {
        // const $stablezblock = $(this);

        // window.console.log($stablezblock.attr('id'));
        // window.console.log($stablezblock);

    });

};