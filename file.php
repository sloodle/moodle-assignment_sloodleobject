<?php
    // This file is part of the Sloodle project (www.sloodle.org)
    
    /**
    * Displays summary information about a Sloodle Object assignment submission.
    *
    * @package sloodleprimdrop
    * @copyright Copyright (c) 2008 Sloodle (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @contributor (various Moodle guys!)
    * @contributor Peter R. Bloomfield
    */

    /** Includes the Moodle configuration */
    require_once("../../../../config.php");
    /** Includes the assignment library. */
    require_once("../../lib.php");
    /** Includes the Sloodle Object assignment type. */
    require_once("assignment.class.php");
 
    $id     = required_param('id', PARAM_INT);      // Course Module ID
    $userid = required_param('userid', PARAM_INT);  // User ID

    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $assignment = get_record("assignment", "id", $cm->instance)) {
        error("Assignment ID was incorrect");
    }

    if (! $course = get_record("course", "id", $assignment->course)) {
        error("Course is misconfigured");
    }

    if (! $user = get_record("user", "id", $userid)) {
        error("User is misconfigured");
    }

    require_login($course->id, false, $cm);

    if (($USER->id != $user->id) && !has_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id))) {
        error("You can not view this assignment");
    }

    if ($assignment->assignmenttype != 'sloodleobject') {
        error("Incorrect assignment type");
    }

    $assignmentinstance = new assignment_sloodleobject($cm->id, $assignment, $cm, $course);

    if ($submission = $assignmentinstance->get_submission($user->id)) {
        print_header(fullname($user,true).': '.$assignment->name);
        
        // Get the Sloodle submission data
        $sloodle_submission = new assignment_sloodleobject_submission();
        $sloodle_submission->load_submission($submission);

        print_simple_box_start('center', '', '', '', 'generalbox', 'dates');
        echo '<table>';
        if ($assignment->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($assignment->timedue).'</td></tr>';
        }
        echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
        echo '    <td class="c1">'.userdate($submission->timemodified);
        // Show the number of prims
        $num_prims = $sloodle_submission->num_prims;
        if ($num_prims == 0) $num_prims = '?';
        echo ' ('.get_string('numprims', 'sloodle', $num_prims).')</td></tr>';
        echo '</table>';
        print_simple_box_end();

        // Display the summary info
        print_simple_box($sloodle_submission->text_summary(), 'center', '100%');
        close_window_button();
        print_footer('none');
    } else {
        print_string('emptysubmission', 'assignment');
    }

?>
