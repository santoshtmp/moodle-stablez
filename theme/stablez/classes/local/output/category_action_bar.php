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

namespace theme_stablez\local\output;

use core_course_category;
use moodle_url;

/**
 * Overridden category_action_bar for theme stablez.
 *
 * @package    theme_stablez
 * @copyright 2025 stablez
 * @author    santoshtmp7 <https://santoshmagar.com.np/>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_action_bar extends \core_course\output\category_action_bar {

    /**
     * Gets the url_select to be displayed in the participants page if available.
     *
     * @param \renderer_base $output
     * @return object|null The content required to render the url_select
     */
    protected function get_category_select(\renderer_base $output): ?object {

        if (!$this->searchvalue && !core_course_category::is_simple_site()) {
            $categories = core_course_category::make_categories_list();
            if (count($categories) > 1) {

                $url = new moodle_url('/course/index.php');
                $options[$url->out()] = get_string('allcoursecategory', 'theme_stablez');

                foreach ($categories as $id => $cat) {
                    $url = new moodle_url($this->page->url, ['categoryid' => $id]);
                    $options[$url->out()] = $cat;
                }
                $currenturl = new moodle_url($this->page->url, ['categoryid' => $this->category->id]);
                $select = new \url_select($options, $currenturl, null);
                $select->set_label(get_string('categories'), ['class' => 'visually-hidden']);
                $select->class .= ' text-truncate w-100';

                return $select->export_for_template($output);
            } else {
                echo "single santosh";
            }
        }

        return null;
    }
}
