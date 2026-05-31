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
 * stablezblocks.
 *
 * @package    block_stablezblocks
 * @copyright  2025 stablezblocks
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_stablezhelpers\local\handler\block_handler;

defined('MOODLE_INTERNAL') || die();

class block_stablezblocks extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_stablezblocks');
    }

    public function specialization() {
        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_stablezblocks');
        } else {
            $this->title = $this->config->title;
        }

        // initially define the value
        if (empty($this->config)) {
            $this->config = new stdClass();
            $this->config->title = $this->title;
            $this->config->stablez_block_type = '';

            // global $DB, $PAGE;
            // $this->instance->subpagepattern  = '2';
            // $DB->update_record('block_instances', $this->instance);
        }
    }

    public function instance_allow_multiple() {
        return true;
    }

    // This line tells Moodle that the block does not have a settings.php file. 
    public function has_config() {
        return false;
    }

    /**   Defines where the block can be added.     */
    public function applicable_formats(): array {
        return [
            'course-view' => true,
            'site' => true,
            'mod' => true,
            'my' => true,
            'all' => true,
            'tag' => false
        ];
    }

    function instance_allow_config() {
        return true;
    }

    public function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }
        global $PAGE;
        $this->content = new stdClass;
        $block_id = isset($this->instance->id) ? $this->instance->id : '';
        $this->config->block_stablez_id = $block_id;
        $PAGE->requires->js_call_amd('block_stablezblocks/block-stablez', 'init', [$block_id]);

        // $this->content->add_class();
        $block_type = $this->config->stablez_block_type ?? '';
        $block_handler = new block_handler($block_type, $this->config);
        $this->content->text = $block_handler->get_block_stablez_content();
        return $this->content;
    }

    /**
     * Hide the block header
     *
     * @return boolean
     */
    public function hide_header() {
        if (isset($this->config->remove_main_heading) && $this->config->remove_main_heading == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add html attributes to block main section 
     */
    public function html_attributes() {
        $block_type = $this->config->stablez_block_type ?? '';
        $block_class = $block_type ? str_replace([' ', '_'], '-', strtolower($block_type)) : '';
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' stablez-block stablez-block-' . $block_class;
        $attributes['full_width_section'] = isset($this->config->full_width_section) ? $this->config->full_width_section : false;
        return $attributes;
    }
}
