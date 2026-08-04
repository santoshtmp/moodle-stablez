<?php
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
 * 
 * @package    theme_stablez
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp7 https://santoshmagar.com.np/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

namespace theme_stablez\hooks;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\before_http_headers;

/**
 * Hook callbacks for theme_stablez for moodle 4.5 and above
 * Other hooks and backward compatable are in lib.php
 *
 * @package    theme_stablez
 * @copyright  santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {


	/**
	 * Callback allowing to add to <head> of the page
	 *
	 * @param \core\hook\output\before_standard_head_html_generation $hook
	 */
	public static function before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook): void {
		global $CFG;
		$output = '';

		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}
		$class_stablezhelpers = \theme_stablez\local\stablezhelpers::class;
		if (class_exists($class_stablezhelpers, true) && is_callable([$class_stablezhelpers, 'set_extra_css_js'])) {
			$class_stablezhelpers::set_extra_css_js('/theme/stablez');
		}

		$hook->add_html($output);
	}

	/**
	 *
	 * @param \core\hook\output\after_standard_main_region_html_generation $hook
	 */
	public static function after_standard_main_region_html_generation(\core\hook\output\after_standard_main_region_html_generation $hook): void {
		global $CFG, $PAGE;
		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}
		$output = "";
		if (function_exists('theme_stablez_get_custom_js')) {
			$output .= theme_stablez_get_custom_js();
		}
		$hook->add_html($output);
	}

	/**
	 * Callback allowing to add contetnt inside the region-main, in the very end
	 *
	 * @param \core\hook\after_config $hook
	 */
	public static function after_config(\core\hook\after_config $hook): void {
		global $CFG;
		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}
		$stablez_adminer_secret = get_config('theme_stablez', 'stablez_adminer_secret');
		if ($stablez_adminer_secret) {
			$CFG->local_adminer_secret = $stablez_adminer_secret;
		}
	}
}
