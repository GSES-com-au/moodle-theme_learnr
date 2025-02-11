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
 * Theme Boost Union - Reset dismissed info banner visibility.
 *
 * @package    theme_learnr
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @copyright  on behalf of Zurich University of Applied Sciences (ZHAW)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');

// Require the necessary libraries.
require_once($CFG->dirroot.'/theme/learnr/lib.php');
require_once($CFG->dirroot.'/theme/learnr/locallib.php');

// Require login and sesskey.
require_login();
require_sesskey();

// Get system context.
$context = context_system::instance();

// Require the necessary capability to configure the theme (or an admin account which has this capability automatically).
require_capability('theme/learnr:configure', $context);

// Get the URL parameters.
$no = required_param('no', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);

// Precheck: If the given info banner number is not valid.
if ($no < 0 || $no > THEME_LEARNR_SETTING_INFOBANNER_COUNT) {
    throw new moodle_exception('error:infobannerdismissnonotvalid', 'theme_learnr');
}

// Precheck: If the given info banner number is not dismissible.
if (get_config('theme_learnr', 'infobanner'.$no.'dismissible') != true) {
    throw new moodle_exception('error:infobannerdismissnonotdismissible', 'theme_learnr');
}

// If we already have a confirmation.
if ($confirm == true) {
    // Reset visibility of the given info banner.
    $resetresult = theme_learnr_infobanner_reset_visibility($no);

    // Redirect with a nice message.
    $redirecturl = new moodle_url('/admin/settings.php',
            ['section' => 'theme_learnr_content'],
            'theme_learnr_infobanners_infobanner');
    if ($resetresult == true) {
        redirect($redirecturl, get_string('infobannerdismisssuccess', 'theme_learnr', ['no' => $no]), null,
                \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($redirecturl, get_string('infobannerdismissfail', 'theme_learnr', ['no' => $no]), null,
                \core\output\notification::NOTIFY_ERROR);
    }

    // Otherwise.
} else {
    // Set page URL.
    $PAGE->set_url('/theme/learnr/settings_infobanner_resetdismissed.php', ['sesskey' => sesskey(), 'no' => $no]);

    // Set page layout.
    $PAGE->set_pagelayout('standard');

    // Set page context.
    $PAGE->set_context($context);

    // Set page title.
    $PAGE->set_title(get_string('infobannerdismissreset', 'theme_learnr'));

    // Start page output.
    echo $OUTPUT->header();

    // Show page heading.
    echo $OUTPUT->heading(get_string('infobannerdismissreset', 'theme_learnr'));

    // Show confirmation message.
    echo get_string('infobannerdismissconfirm', 'theme_learnr', ['no' => $no]);

    // Start buttons.
    echo html_writer::start_tag('div', ['class' => 'mt-2']);

    // Show confirm button.
    $confirmurl = new moodle_url('/theme/learnr/settings_infobanner_resetdismissed.php',
            ['sesskey' => sesskey(), 'no' => $no, 'confirm' => 1]);
    echo html_writer::link($confirmurl,
            get_string('confirm', 'core', null, true),
            ['class' => 'btn btn-primary mr-3', 'role' => 'button']);

    // Show cancel button.
    $cancelurl = new moodle_url('/admin/settings.php',
            ['section' => 'theme_learnr_content'],
            'theme_learnr_infobanners_infobanner');
    echo html_writer::link($cancelurl,
            get_string('cancel', 'core', null, true),
            ['class' => 'btn btn-secondary', 'role' => 'button']);

    // End buttons.
    echo html_writer::end_tag('div');

    // Finish page.
    echo $OUTPUT->footer();
}
