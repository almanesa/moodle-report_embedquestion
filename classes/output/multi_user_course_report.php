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
 * Renderable for the Embedded questions progress report for staff
 * who can see data for several users at course level.
 *
 * @package   report_embedquestion
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_embedquestion\output;
use report_embedquestion\form\filter;
use report_embedquestion\latest_attempt_table;
use report_embedquestion\report_display_options;
use report_embedquestion\utils;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderable for the Embedded questions progress report for staff
 * who can see data for several users at course level.
 *
 * @package   report_embedquestion
 * @copyright 2019 The Open University
 */
class multi_user_course_report {

    /** @var int the id of the course we are showing the report for. */
    protected $courseid;

    /** @var int the id of the group we are showing the report for. 0 for all users. */
    protected $groupid;

    /** @var \context the course context. */
    protected $context;

    /**
     * Constructor.
     *
     * @param int $courseid the id of the course we are showing the report for.
     * @param int $groupid the id of the group we are showing the report for. 0 for all users.
     */
    public function __construct(int $courseid, int $groupid, \context $context) {
        $this->courseid = $courseid;
        $this->groupid = $groupid;
        $this->context = $context;
    }

    /**
     * Get a suitable page title.
     *
     * @return string the title.
     */
    public function get_title(): string {
        return get_string('coursereporttitle', 'report_embedquestion',
                $this->context->get_context_name(false, false));
    }

    /**
     * Display or download the report.
     * @param string|null $download
     */
    public function display_download_content($download = null) {
        global $COURSE;

        // TODO: This part of code will be moved to only one place in #385941.
        $filterform = new filter(utils::get_url(['courseid' => $this->courseid])->out(false), ['context' => $this->context]);
        $displayoptions = new report_display_options($this->courseid, null);
        if ($fromform = $filterform->get_data()) {
            $displayoptions->process_settings_from_form($fromform);
        } else {
            $displayoptions->process_settings_from_params();
        }
        $filterform->set_data($displayoptions->get_initial_form_data());

        $filename = $COURSE->shortname . '_' . str_replace(' ', '_', $this->get_title());
        if (!$download) {
            // Output the group selector.
            groups_print_course_menu($COURSE, utils::get_url(['courseid' => $this->courseid]));
            $table = new latest_attempt_table($this->context, $this->courseid, null, $displayoptions);
            // Display the filter form.
            echo $filterform->render();
            utils::allow_downloadability_for_attempt_table($table, $this->get_title(), $this->context);
        } else {
            $table = new latest_attempt_table($this->context, $this->courseid, null, $displayoptions, $download);
            $table->is_downloading($download, $filename);
            if ($table->is_downloading()) {
                raise_memory_limit(MEMORY_EXTRA);
            }
        }
        $table->setup();
        $table->out($displayoptions->pagesize, true);
    }
}
