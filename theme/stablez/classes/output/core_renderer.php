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
 * @package   theme_stablez   
 * @copyright 2025 stablez
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_stablez\output;

defined('MOODLE_INTERNAL') || die;

use core_block\output\block_contents;
use moodle_url;
use stdClass;
use theme_stablez\local\service\theme_settings_service;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_stablez   
 * @copyright 2025 stablez
 * @author    santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * core\output\core_renderer
 */

//  class core_renderer extends \core\output\core_renderer
class core_renderer extends \theme_boost\output\core_renderer {

	/**
	 * 
	 */
	public function get_theme_stablez_logo_description() {
		return format_text(theme_settings_service::get_instance()->setting('logo_description'));
	}

	/**
	 * 
	 */
	public function get_theme_stablez_login_card_popup() {
		return (int)theme_settings_service::get_instance()->setting('login_card_popup');
	}

	/**
	 * 
	 */
	public function get_logo_url_out() {
		return theme_settings_service::get_instance()->setting('logo', 'logo');
	}

	/**
	 * Renders the "breadcrumb" for all pages in boost.
	 *
	 * @return string the HTML for the navbar.
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function navbar(): string {
		// $newnav = new \theme_boost\boostnavbar($this->page);
		$newnav = new \theme_stablez\local\breadcrumb_navbar($this->page);
		return $this->render_from_template('core/navbar', $newnav);
	}

	// /**
	//  * Construct a user menu, returning HTML that can be echoed out by a
	//  * layout file.
	//  *
	//  * @param stdClass $user A user object, usually $USER.
	//  * @param bool $withlinks true if a dropdown should be built.
	//  * @return string HTML fragment.
	//  * 
	//  */
	// public function user_menu($user = null, $withlinks = null){}


	// /**
	//  * Wrapper for header elements.
	//  *
	//  * @return string HTML to display the main header.
	//  * 
	//  */
	// public function full_header() {
	// }


	/**
	 * Returns standard navigation between activities in a course.
	 * 
	 * \core\output\core_renderer
	 * 
	 * @return string the navigation HTML.
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function activity_navigation() {
		// First we should check if we want to add navigation.
		$context = $this->page->context;
		if (
			($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
			|| $context->contextlevel != CONTEXT_MODULE
		) {
			return '';
		}

		// If the activity is in stealth mode, show no links.
		if ($this->page->cm->is_stealth()) {
			return '';
		}

		$course = $this->page->cm->get_course();

		//  stablez_activity_navigation value can be  [0 => 'Default', 1 => 'Always show', 2 => 'Always hide']
		$stablez_activity_navigation = get_config('theme_stablez', 'stablez_activity_navigation');
		if ($stablez_activity_navigation == '2') {
			return '';
		} else if ($stablez_activity_navigation == '0') {
			$courseformat = course_get_format($course);

			// If the theme implements course index and the current course format uses course index and the current
			// page layout is not 'frametop' (this layout does not support course index), show no links.
			if (
				$this->page->theme->usescourseindex && $courseformat->uses_course_index() &&
				$this->page->pagelayout !== 'frametop'
			) {
				return '';
			}
		}

		// check if it is frontpage 
		if ($course->id == '1') {
			return '';
		}

		// Get a list of all the activities in the course.
		$modules = get_fast_modinfo($course->id)->get_cms();

		// Put the modules into an array in order by the position they are shown in the course.
		$mods = [];
		$activitylist = [];
		foreach ($modules as $module) {
			// Only add activities the user can access, aren't in stealth mode, are of a type that is visible on the course,
			// and have a url (eg. mod_label does not).
			if (!$module->uservisible || $module->is_stealth() || empty($module->url) || !$module->is_of_type_that_can_display()) {
				continue;
			}
			$mods[$module->id] = $module;

			// No need to add the current module to the list for the activity dropdown menu.
			if ($module->id == $this->page->cm->id) {
				continue;
			}
			// Module name.
			$modname = $module->get_formatted_name();
			// Display the hidden text if necessary.
			if (!$module->visible) {
				$modname .= ' ' . get_string('hiddenwithbrackets');
			}
			// Module URL.
			$linkurl = new moodle_url($module->url, ['forceview' => 1]);
			// Add module URL (as key) and name (as value) to the activity list array.
			$activitylist[$linkurl->out(false)] = $modname;
		}

		$nummods = count($mods);

		// If there are only one or fewer mods then do nothing.
		if ($nummods <= 1) {
			return '';
		}

		// Get an array of just the course module ids used to get the cmid value based on their position in the course.
		$modids = array_keys($mods);

		// Get the position in the array of the course module we are viewing.
		$position = array_search($this->page->cm->id, $modids);

		$prevmod = null;
		$nextmod = null;

		// Check if we have a previous mod to show.
		if ($position > 0) {
			$prevmod = $mods[$modids[$position - 1]];
		}

		// Check if we have a next mod to show.
		if ($position < ($nummods - 1)) {
			$nextmod = $mods[$modids[$position + 1]];
		}

		$activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
		$renderer = $this->page->get_renderer('core', 'course');
		return $renderer->render($activitynav);
	}

	/**
	 * Returns the moodle_url for the favicon.
	 *
	 * @since Moodle 2.5.1 2.6
	 * @return moodle_url The moodle_url for the favicon
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function favicon() {
		$favicon = theme_settings_service::get_instance()->setting('favicon', 'favicon');
		if ($favicon) {
			return $favicon;
		}

		return parent::favicon();
	}

	/**
	 * Return the site's logo URL, if any.
	 *
	 * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
	 * @param int $maxheight The maximum height, or null when the maximum height does not matter.
	 * @return moodle_url|false
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function get_logo_url($maxwidth = null, $maxheight = 200) {
		$theme_logo = get_config('theme_stablez', 'logo');
		if ($theme_logo) {
			// Use $CFG->themerev to prevent browser caching when the file changes.
			$logo = moodle_url::make_pluginfile_url(
				\context_system::instance()->id,
				'theme_stablez',
				'logo',
				'',
				theme_get_revision(),
				$theme_logo
			);
			if ($logo) {
				return $logo;
			}
		}

		return parent::get_logo_url($maxwidth, $maxheight);
	}

	/**
	 * Returns HTML attributes to use within the body tag. This includes an ID and classes.
	 *
	 * @since Moodle 2.5.1 2.6
	 * @param string|array $additionalclasses Any additional classes to give the body tag,
	 * @return string
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function body_attributes($additionalclasses = []) {
		if (!is_array($additionalclasses)) {
			$additionalclasses = explode(' ', $additionalclasses);
		}
		$additionalclasses[] = "stablez-style";
		return ' id="' . $this->body_id() . '" class="' . $this->body_css_classes($additionalclasses) . '"';
	}

	/**
	 * Prints a nice side block with an optional header.
	 *
	 * @param block_contents $bc HTML for the content
	 * @param string $region the region the block is appearing in.
	 * @return string the HTML to be output.
	 * 
	 * Modified since Moodle 5.2.0
	 * 
	 */
	public function block(block_contents $bc, $region) {
		$bc = clone($bc); // Avoid messing up the object passed in.
		if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
			$bc->collapsible = block_contents::NOT_HIDEABLE;
		}

		$id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
		$context = new stdClass();
		$context->skipid = $bc->skipid;
		$context->blockinstanceid = $bc->blockinstanceid ?: uniqid('fakeid-');
		$context->dockable = $bc->dockable;
		$context->id = $id;
		$context->hidden = $bc->collapsible == block_contents::HIDDEN;
		$context->skiptitle = strip_tags($bc->title);
		$context->showskiplink = !empty($context->skiptitle);
		$context->arialabel = $bc->arialabel;
		$context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : '';
		$context->class = $bc->attributes['class'];
		$context->type = $bc->attributes['data-block'];
		$context->title = (string) $bc->title;
		$context->showtitle = $context->title !== '';
		$context->content = $bc->content;
		$context->annotation = $bc->annotation;
		$context->footer = $bc->footer;
		$context->hascontrols = !empty($bc->controls);
		if ($context->hascontrols) {
			$context->controls = $this->block_controls($bc->controls, $id);
		}
		$context->has_block_title = ($context->title || $context->hascontrols) ? true : false;
		$context->is_full_width = isset($bc->attributes['full_width_section']) ? $bc->attributes['full_width_section'] : false;

		return $this->render_from_template('core/block', $context);
	}
}
