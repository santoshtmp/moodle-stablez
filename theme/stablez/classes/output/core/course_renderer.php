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

namespace theme_stablez\output\core;

use core\lang_string;
use core\output\html_writer;
use core\output\theme_config;
use core_course_category;
use core_course_list_element;
use coursecat_helper;
use local_stablezhelpers\local\service\course_service;
use moodle_url;
use stdClass;
use theme_stablez\local\service\theme_settings_service;
use theme_stablez\local\stablezhelpers;

defined('MOODLE_INTERNAL') || die;


/**
 * Class to override the core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class course_renderer extends \core_course_renderer {

    /**
     * 
     * Modified since Moodle 5.2.0
     * 
     * Renders HTML to display particular course category - list of it's subcategories and courses
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|core_course_category $category
     * @return string
     */
    public function course_category($category) {
        global $CFG;
        $usertop = core_course_category::user_top();
        if (empty($category)) {
            $coursecat = $usertop;
        } else if (is_object($category) && $category instanceof core_course_category) {
            $coursecat = $category;
        } else {
            $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        }
        $site = get_site();
        // $actionbar = new \core_course\output\category_action_bar($this->page, $coursecat);
        $actionbar = new \theme_stablez\local\output\category_action_bar($this->page, $coursecat);
        $output = $this->render_from_template('core_course/category_actionbar', $actionbar->export_for_template($this));

        if (core_course_category::is_simple_site()) {
            // There is only one category in the system, do not display link to it.
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title($strfulllistofcourses);
        } else if (!$coursecat->id || !$coursecat->is_uservisible()) {
            $strcategories = get_string('categories');
            $this->page->set_title($strcategories);
        } else {
            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title($strfulllistofcourses);
        }

        // Print current category description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $output .= $this->box($description, array('class' => 'generalbox info'));
        }


        /*
        * ================================================================
        * TOP LEVEL /course/index.php
        * ================================================================
        *
        * When there is no category selected, display ALL courses from
        * all categories in one list.
        */
        if (!$coursecat->id) {
            $theme = theme_settings_service::get_instance()->theme;
            $view_all_courses_ono_category = $theme->settings->view_all_courses_ono_category ?? '';
            if ($view_all_courses_ono_category == '1') {
                $this->page->set_heading(get_string('courses'));

                // base url
                $baseurl = new moodle_url('/course/index.php');
                // Pagination.
                $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
                $page = optional_param('page', 0, PARAM_INT);
                // Prevent invalid values.
                if ($perpage <= 0) {
                    $perpage = $CFG->coursesperpage;
                }
                if ($page < 0) {
                    $page = 0;
                }

                /*
            * Get all courses visible to the current user.
            *
            * recursive = true means courses from child categories
            * are also included.
            */
                $courses = $coursecat->get_courses(
                    [
                        'recursive' => true,
                        'idonly' => false,
                    ]
                );
                // Remove the Moodle front page course.
                if (isset($courses[SITEID])) {
                    unset($courses[SITEID]);
                }
                // Total number of courses before pagination.
                $totalcourses = count($courses);

                // Apply Pagination.
                $offset = $page * $perpage;
                $courses = array_slice(
                    $courses,
                    $offset,
                    $perpage,
                    true
                );

                // Course display options.
                $coursedisplayoptions = [
                    'limit' => $perpage,
                    'offset' => $offset,
                    'paginationurl' => $baseurl,
                ];

                // Preserve perpage in pagination URL.
                if ($perpage != $CFG->coursesperpage) {
                    $coursedisplayoptions['paginationurl']->param(
                        'perpage',
                        $perpage
                    );
                }

                /*
            * Render ALL courses as one course list.
            *
            * The category tree is intentionally NOT rendered here.
            */
                if (!empty($courses)) {
                    $output .= $this->courses_list(
                        $courses,
                        $coursedisplayoptions,
                        'courses-list-wrapper',
                    );
                } else {
                    $output .= $this->notification(
                        get_string('nocourses'),
                        \core\output\notification::NOTIFY_INFO
                    );
                }

                // Render pagination if necessary.
                if ($totalcourses > $perpage) {
                    if ($perpage != $CFG->coursesperpage) {
                        $baseurl->param('perpage', $perpage);
                    }
                    $output .= $this->paging_bar(
                        $totalcourses,
                        $page,
                        $perpage,
                        $baseurl
                    );
                }

                return $output;
            }
        }

        /*
        * ================================================================
        * CATEGORY PAGE
        * ================================================================
        *
        * If a specific category was selected, keep Moodle's normal output.
        * 
        */

        // Prepare parameters for courses and categories lists in the tree
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
            ->set_attributes(array('class' => 'category-browse category-browse-' . $coursecat->id));

        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->get_children_count()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->get_courses_count()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        return $output;
    }

    /**
     * 
     * Modified since Moodle 5.2.0
     * 
     * Renders the list of courses
     *
     * This is internal function, please use {@link core_course_renderer::courses_list()} or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        global $CFG, $OUTPUT;
        $theme = theme_settings_service::get_instance()->theme;
        $courses_view = $theme->settings->courses_view;
        // defaulr or card
        if ($courses_view == 'default') {
            return parent::coursecat_courses($chelper, $courses, $totalcount);
        }
        // 
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // prepare content of paging bar if it is needed
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // there are more results that can fit on one page
            if ($paginationurl) {
                // the option paginationurl was specified, display pagingbar
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link
                // $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $btn_context = [
                    'button_wrapper_class' => 'stablez-view-more',
                    'button_class' => 'btn btn-primary view-all-course',
                    'button_link' => $viewmoreurl,
                    'button_label' => get_string('view_all_course', 'theme_stablez'),
                    // 'icon_link' => $CFG->wwwroot . '/theme/stablez/pix/icons/arrow-right.svg',
                    'pix_svg_icon' => stablezhelpers::get_svg_pix_icon_content('btn-arrow-icon')
                ];
                $btn = $OUTPUT->render_from_template('theme_stablez/parts/button', $btn_context);

                $morelink = html_writer::tag(
                    'div',
                    $btn,
                    ['class' => 'paging paging-morelink']
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of courses content
        $content = "";
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content .= html_writer::start_tag('div', $attributes);
        $content .= html_writer::start_tag('div', ['class' => 'stablez-courses-wrapper']);
        $coursecount = 0;
        foreach ($courses as $course) {
            $coursecount++;
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
        }
        $content .= html_writer::end_tag('div'); //.stablez-courses-wrapper

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // .courses
        return $content;
    }

    /**
     * 
     * Modified since Moodle 5.2.0
     * 
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $OUTPUT;
        $theme = theme_settings_service::get_instance()->theme;
        $content = '';
        try {
            $courses_view = $theme->settings->courses_view;
            // defaulr or card
            if ($courses_view == 'default') {
                return parent::coursecat_coursebox($chelper, $course, $additionalclasses);
            }
            if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
                return '';
            }

            $template_content = course_service::course_card_info($course->id, true);
            $template_content['datatype'] = self::COURSECAT_TYPE_COURSE;
            $template_content['pix_svg_icon'] = stablezhelpers::get_svg_pix_icon_content('btn-arrow-icon');
            $content = $OUTPUT->render_from_template('theme_stablez/parts/course-card', $template_content);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }

        return $content;
    }


    /**
     * 
     * Modified since Moodle 5.2.0
     * 
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     * @return string
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
        global $CFG, $OUTPUT;
        $content = '';
        try {
            $theme = theme_settings_service::get_instance()->theme;
            $courses_view = $theme->settings->courses_view;
            // defaulr or card
            if ($courses_view == 'default') {
                return parent::coursecat_coursebox_content($chelper, $course);
            }

            $template_content =  course_service::course_card_info($course->id, true);
            $content = $OUTPUT->render_from_template('theme_stablez/parts/course-card', $template_content);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
        return $content;
    }

    /**
     * 
     * Modified since Moodle 5.2.0
     * 
     * Renders the list of subcategories in a category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth) {
        global $CFG;
        $subcategories = array();
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
            // to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        // prepare content of paging bar or more link if it is needed
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // the option 'paginationurl was specified, display pagingbar
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage))
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)
                    ), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink')
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), array('class' => 'paging paging-showperpage'));
        }

        // display list of subcategories
        $content = html_writer::start_tag('div', array('class' => 'subcategories stablez-subcategories'));

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_category($chelper, $subcategory, $depth + 1);
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Modified since Moodle 5.2.0
     * 
     * Returns HTML to print list of available courses for the frontpage 
     * 
     * @return string
     */
    public function frontpage_available_courses() {
        global $CFG;
        $classes = "";
        $available_courses = get_config('theme_stablez', 'available_courses');
        if ($available_courses === 'hide') {
            return;
        } elseif ($available_courses === 'default') {
            return parent::frontpage_available_courses();
        } else {
            $classes = " stablez-frontpage-course-list stablez-frontpage-course-slider";
        }
        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->set_courses_display_options(array(
            'recursive' => true,
            'limit' => $CFG->frontpagecourselimit,
            'viewmoreurl' => new moodle_url('/course/index.php'),
            'viewmoretext' => new lang_string('fulllistofcourses')
        ));

        $chelper->set_attributes(array('class' => trim($classes)));
        $courses = core_course_category::top()->get_courses($chelper->get_courses_display_options());
        $totalcount = core_course_category::top()->get_courses_count($chelper->get_courses_display_options());
        // if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
        //     // Print link to create a new course, for the 1st available category.
        //     return $this->add_new_course_button();
        // }
        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }
}
