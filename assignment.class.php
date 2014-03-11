<?php
    // This file is part of the Sloodle project (www.sloodle.org)
    
    /**
    * This file defines the "Sloodle Object" assignment sub-type.
    * It allows students to submit 3d objects in Second Life as Moodle assignments.
    *
    * @package sloodleprimdrop
    * @copyright Copyright (c) 2008 Sloodle (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @contributor (various Moodle guys!)
    * @contributor Peter R. Bloomfield
    */

/** Attempt to include the Sloodle configuration. */
require_once($CFG->dirroot.'/mod/sloodle/sl_config.php');
/** Include the general Sloodle functions. */
require_once($CFG->dirroot.'/mod/sloodle/lib/general.php');

/** Include the base assignment class, if necessary. */
require_once($CFG->dirroot.'/mod/assignment/lib.php');

// The assignment plugin seems to be calling this with require or include rather than require_once or include_once.
// Check if the class exists in case we've already defined it.
// mod/assignment/view.php?id=21
if ( !class_exists( 'assignment_sloodleobject' ) ) {

/**
 * Extend the base assignment class for assignments where you submit an SL object in-world.
 * This has been modified from the "assignment_online" type.
 * @package sloodle
 */
class assignment_sloodleobject extends assignment_base {

    function assignment_sloodleobject($cmid=0, $assignment=NULL, $cm=NULL, $course=NULL) {
        parent::assignment_base($cmid, $assignment, $cm, $course);
        $this->type = 'sloodleobject';
    }

    function view() {
        
        // Bring in the global user data
        global $USER;

        // Check that this user can view assignments
        $context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
        require_capability('mod/assignment:view', $context);
        
        // Fetch the submission data
        $submission = $this->get_submission();
        $sloodle_submission = new assignment_sloodleobject_submission();
        $sloodle_submission->load_submission($submission);

        $this->view_header();
        $this->view_intro();
        $this->view_dates();
        
        // Display a texture summary of the submission
        $this->print_box_start_compat('center', '70%', '', 0, 'generalbox', 'online');
        $sloodle_submission->text_summary(false);
        $this->print_box_end_compat();
        
        
        $this->view_feedback();
        $this->view_footer();
    }

    /*
     * Display the assignment dates
     */
    function view_dates() {
        // Bring in the global user and configuration data
        global $USER, $CFG;

        // Make sure the time available and time due dates are set
        if (!$this->assignment->timeavailable && !$this->assignment->timedue) {
            return;
        }
        // Start a display box
        $this->print_box_start_compat('center', '', '', 0, 'generalbox', 'dates');
        echo '<table>';
        // Display the time the assignment is available from
        if ($this->assignment->timeavailable) {
            echo '<tr><td class="c0">'.get_string('availabledate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timeavailable).'</td></tr>';
        }
        // Display the time the assignment is due by
        if ($this->assignment->timedue) {
            echo '<tr><td class="c0">'.get_string('duedate','assignment').':</td>';
            echo '    <td class="c1">'.userdate($this->assignment->timedue).'</td></tr>';
        }
        // Is there a submission by this user?
        $submission = $this->get_submission($USER->id);
        if ($submission) {
            // Convert the submission to a Sloodle assignment object
            $sloodle_submission = new assignment_sloodleobject_submission();
            $sloodle_submission->load_submission($submission);
        
            // Display the date it was last updated
            echo '<tr><td class="c0">'.get_string('lastedited').':</td>';
            echo '    <td class="c1">'.userdate($submission->timemodified);
            
            // Display the number of prims in the submission
            $num_prims = $sloodle_submission->num_prims;
            if ($num_prims == 0) $num_prims = '?';
            echo ' ('.get_string('numprims', 'sloodle', $num_prims).')</td></tr>';
        }
        echo '</table>';
        $this->print_box_end_compat();
    }

    /**
    * Update the submission with the provided data.
    * @param int $userid Integer ID of the user making the submission
    * @param assignment_sloodleobject_submission $data A structure containing data about the Sloodle assignment submission
    * @return bool True if successful, or false otherwise.
    */
    function update_submission($userid, $data)
    {
        $submission = $this->get_submission($userid, true);

        $update = new object();
        $update->id           = $submission->id;
        $update->data1        = "{$data->obj_name}|{$data->num_prims}";
        $update->data2        = "{$data->primdrop_name}|{$data->primdrop_uuid}|{$data->primdrop_region}|{$data->primdrop_pos}";
        $update->timemodified = time();

        return sloodle_update_record('assignment_submissions', $update);
    }


    function print_student_answer($userid, $return=true){
        global $CFG;
        $text = '';
        
        if (!($submission = $this->get_submission($userid))) {
            $text = '';
        } else {
            // Output the Submission data
            $sloodle_submission = new assignment_sloodleobject_submission();
            $sloodle_submission->load_submission($submission);
            
            //$text = '<b>'.shorten_text(trim(strip_tags($sloodle_submission->obj_name)), 20).'</b><br>';
            
            $text = '<div class="files">'.
                  '<img src="'.$CFG->pixpath.'/f/html.gif" class="icon" alt="html" />'.
                  link_to_popup_window ($CFG->wwwroot.'/mod/assignment/type/sloodleobject/file.php?id='.$this->cm->id.'&userid='.
                  $submission->userid, 'file'.$userid, shorten_text(trim(strip_tags($sloodle_submission->obj_name)), 20), 450, 580,
                  get_string('submission', 'assignment'), 'none', true).
                  '</div>';
        }
        
        if ($return) return $text;
        echo $text;
    }
    
    
    function print_user_files($userid, $return=false) {
        global $CFG;
        if (!$submission = $this->get_submission($userid)) {
            return '';
        }
        
        // Construct a Sloodle submission object
        $sloodle_submission = new assignment_sloodleobject_submission();
        $sloodle_submission->load_submission($submission);

        // Display the number of prims
        $num_prims = $sloodle_submission->num_prims;
        if ($num_prims == 0) $num_prims = '?';
        $this->print_box_start_compat('center', '', '', 0, 'generalbox', 'wordcount');
        echo ' ('.get_string('numprims', 'sloodle', $num_prims).')';
        $this->print_box_end_compat();
        
        // Display the text summary of this submission
        $this->print_box_compat($sloodle_submission->text_summary());
    }
    

    function preprocess_submission(&$submission) {
        
    }

    function setup_elements(&$mform) {
        global $CFG, $COURSE;

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'resubmit', get_string("allowresubmit", "assignment"), $ynoptions);
        //$mform->setHelpButton('resubmit', array('resubmit', get_string('allowresubmit', 'assignment'), 'assignment'));
        $mform->setDefault('resubmit', 0);

        $mform->addElement('select', 'emailteachers', get_string("emailteachers", "assignment"), $ynoptions);
        //$mform->setHelpButton('emailteachers', array('emailteachers', get_string('emailteachers', 'assignment'), 'assignment'));
        $mform->setDefault('emailteachers', 0);
    }

    function print_box_compat($message) {

        global $OUTPUT;
        if ($OUTPUT && method_exists($OUTPUT, 'box')) {
            print $OUTPUT->box($message);
        } else {
            print_simple_box($message);
        }

    }

    function print_box_start_compat($p1, $p2, $p3, $p4, $p5, $p6) {

        global $OUTPUT;
        if ($OUTPUT && method_exists($OUTPUT, 'box_start')) {
            print $OUTPUT->box_start();
        } else {
            print_simple_box_start($p1, $p2, $p3, $p4, $p5, $p6);
        }
    }

    function print_box_end_compat() {

        global $OUTPUT;
        if ($OUTPUT && method_exists($OUTPUT, 'box_end')) {
            print $OUTPUT->box_end();
        } else {
            print_simple_box_end();
        }

    }

}


/**
* Defines a submission for a Sloodle assignment.
* @package sloodle
*/
class assignment_sloodleobject_submission
{
    /**
    * Indicates whether or not a submission is loaded
    * @var bool
    * @access public
    */
    var $is_loaded = false;

    /**
    * Name of the object which has been submitted.
    * @var string
    * @access public
    */
    var $obj_name = '';
    
    /**
    * Number of prims in the object which has been submitted
    * @var int
    * @access public
    */
    var $num_prims = 0;
    
    /**
    * Name of the PrimDrop object into which this object was submitted.
    * @var string
    * @access public
    */
    var $primdrop_name = '';
    
    /**
    * UUID of the PrimDrop object into which this object was submitted.
    * @var string
    * @access public
    */
    var $primdrop_uuid = '';
    
    /**
    * Name of the region where this object was submitted
    * @var string
    * @access public
    */
    var $primdrop_region = '';
    
    /**
    * Position where this object was submitted (as a vector, "<x,y,z>")
    * @var string
    * @access public
    */
    var $primdrop_pos = '';
    
    
    /**
    * Parses the data from a Submission database record object.
    * @var object $submission The submission database record object.
    */
    function load_submission($submission)
    {
        // Make sure the submission is valid
        $this->is_loaded = false;
        if ($submission == false || $submission == null) return;
    
        // Parse the data lines
        $object_data = explode('|', $submission->data1);
        $primdrop_data = explode('|', $submission->data2);
        
        // Make sure there's enough data in both
        if (count($object_data) < 2 || count($primdrop_data) < 4) return;
        
        // Extract the data items
        $this->obj_name = $object_data[0];
        $this->num_prims = (int)$object_data[1];
        $this->primdrop_name = $primdrop_data[0];
        $this->primdrop_uuid = $primdrop_data[1];
        $this->primdrop_region = $primdrop_data[2];
        $this->primdrop_pos = $primdrop_data[3];
        
        $this->is_loaded = true;
    }
    
    /**
    * Sets the position as 3 components.
    * @param float x The X coordinate
    * @param float y The Y coordinate
    * @param float z The Z coordinate
    */
    function set_pos($x, $y, $z)
    {
        $this->primdrop_pos = "<$x,$y,$z>";
    }
    
    /**
    * Gets the X coordinate of the position
    * @return float The X coordinate of the position
    */
    function get_x()
    {
        $arr = sloodle_vector_to_array($this->primdrop_pos);
        if (!$arr) return 0.0;
        return (float)$arr['x'];
    }
    
    /**
    * Gets the Y coordinate of the position
    * @return float The Y coordinate of the position
    */
    function get_y()
    {
        $arr = sloodle_vector_to_array($this->primdrop_pos);
        if (!$arr) return 0.0;
        return (float)$arr['y'];
    }
    
    /**
    * Gets the Z coordinate of the position
    * @return float The Z coordinate of the position
    */
    function get_z()
    {
        $arr = sloodle_vector_to_array($this->primdrop_pos);
        if (!$arr) return 0.0;
        return (float)$arr['z'];
    }
    
    /**
    * Construct a text summary of this submission.
    * @param bool $return If TRUE (default) then the text will be submitted instead of printed.
    * @return string If parameter $return was TRUE, then it returns the string. Otherwise, an empty string.
    */
    function text_summary($return = true)
    {
        // Make sure something is loaded
        if (!$this->is_loaded) {
            $text = get_string('emptysubmission', 'assignment');
        } else {
            $text = "Object name: <b>{$this->obj_name}</b><br><br>";
            $text .= "Submitted to: {$this->primdrop_name}<br>";
            $text .= " <i>({$this->primdrop_uuid})</i><br><br>";
            
            $arr = sloodle_vector_to_array($this->primdrop_pos);
            if (!$arr) $arr = array('x'=>0, 'y'=>0, 'z'=>0);
            else {
                $arr['x'] = round($arr['x']);
                $arr['y'] = round($arr['y']);
                $arr['z'] = round($arr['z']);
            }
            
            $loc = "secondlife://{$this->primdrop_region}/{$arr['x']}/{$arr['y']}/{$arr['z']}";
            $text .= "<b><a href=\"$loc\">$loc</a></b><br>";
        }
        
        if ($return) return $text;
        echo $text;
        return '';
    }
}

}

?>
