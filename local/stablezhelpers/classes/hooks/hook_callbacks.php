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
 * Hook callbacks for local_stablezhelpers for moodle 4.5 and above
 * Other hooks and backward compatable are in lib.php
 * 
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

namespace local_stablezhelpers\hooks;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\before_http_headers;
use local_stablezhelpers\external\course\general_section_setting;
use local_stablezhelpers\local\handler\google_translate_handler;

/**
 * Hook callbacks for local_stablezhelpers for moodle 4.5 and above
 * Other hooks and backward compatable are in lib.php
 *
 * @package    local_stablezhelpers
 * @copyright  santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {


	/**
	 * Callback allowing to before_http_headers
	 *
	 * @param \core\hook\output\before_http_headers $hook
	 */
	public static function before_http_headers(before_http_headers $hook): void {
		global $CFG;
		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}
		$class_stablezhelpers = \local_stablezhelpers\local\stablezhelpers::class;
		if (class_exists($class_stablezhelpers, true) && is_callable([$class_stablezhelpers, 'security_header'])) {
			$class_stablezhelpers::security_header();
		}
	}

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
		$hook->add_html($output);
	}

	/**
	 * Callback allowing to add contetnt inside the region-main, in the very end
	 *
	 * @param \core\hook\output\before_footer_html_generation $hook
	 */
	public static function before_footer_html_generation(\core\hook\output\before_footer_html_generation $hook): void {
		global $CFG;
		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}
		$output = "";
		$hook->add_html($output);
	}

	/**
	 *
	 * @param \core\hook\output\before_standard_footer_html_generation $hook
	 */
	public static function before_standard_footer_html_generation(\core\hook\output\before_standard_footer_html_generation $hook): void {
		global $CFG, $PAGE;
		if (during_initial_install() || isset($CFG->upgraderunning)) {
			// Do nothing during installation or upgrade.
			return;
		}

		// course general_section settings
		general_section_setting::setting_hideshow();
		
		// Apply google translate if enable
		google_translate_handler::google_translate_lang();

		// add to footer
		$output = "";
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
			// $output .= theme_stablez_get_custom_js();
		}

		$hook->add_html($output);
	}

	/**
	 * Callback allowing to add contetnt inside the region-main, in the very end
	 *
	 * @param \core\hook\after_config $hook
	 */
	public static function after_config(\core\hook\after_config $hook): void {
	}

	/**
	 * Allow other plugins/themes to modify content view template data.
	 *
	 * @param array $templatecontent Reference to the template data
	 * @param \stdClass $content The content record
	 * @param int $id Content ID
	 */
	public static function modify_content_view_template(array &$templatecontent, \stdClass $content, int $id): void {
		// Call all plugins that implement the callback
		$pluginsfunction = get_plugins_with_function('local_stablezhelpers_modify_content_view_template');

		foreach ($pluginsfunction as $plugins) {
			foreach ($plugins as $pluginfunction) {
				$pluginfunction($templatecontent, $content, $id);
			}
		}
	}
}
