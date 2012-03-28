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
 * True-false question definition class.
 *
 * @package    qtype
 * @subpackage simulationceis
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a true-false question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationceis_question extends question_graded_automatically {
    public $rightanswer;
    public $truefeedback;
    public $falsefeedback;
    public $trueanswerid;
    public $falseanswerid;

    public function get_expected_data() {
    	echo 'get_expected_data';
        return array('answer' => PARAM_INTEGER);
    }

    public function get_correct_response() {
        $var = array('answer' => (int) $this->rightanswer);
        $var2 = array('answer' => PARAM_INTEGER);
    	//print_r($var);
		//print_r($var2);
		global $CFG;
		require_js($CFG->wwwroot.'/question/type/simulationceis/js/interface.js');
        return array('answer' => (int) $this->rightanswer);
        /*
        $response = array();
        foreach ($this->places as $place => $notused) {
            $response[$this->field($place)] = $this->get_right_choice_for($place);
        }
        return $response;*/
        
    }

    public function summarise_response(array $response) {
        if (!array_key_exists('answer', $response)) {
            return null;
        } else if ($response['answer']) {
            return get_string('true', 'qtype_simulationceis');
        } else {
            return get_string('false', 'qtype_simulationceis');
        }
        	/*echo '</br>';
	    	echo '************ summarise_response $response *********************';
	    	echo '</br>';
	    	print_r($response);
	    	echo '</br>';
	    	echo '************ summarise_response $response *********************';
	    	echo '</br>';*/
    }

	public function get_question_summary() {
        /*echo '</br>';
    	echo '************ get_question_summary $question *********************';
    	echo '</br>';
    	print_r($question);
    	echo '</br>';
    	echo '************ get_question_summary $question *********************';
    	echo '</br>';*/
        $groups = array();
        foreach ($this->choices as $group => $choices) {
            $cs = array();
            foreach ($choices as $choice) {
                $cs[] = html_to_text($choice->text, 0, false);
            }
            $groups[] = '[[' . $group . ']] -> {' . implode(' / ', $cs) . '}';
        }
        return $question . '; ' . implode('; ', $groups);
    }
    
    
    public function classify_response(array $response) {
    	/*echo '</br>';
    	echo '************ classify_response $response *********************';
    	echo '</br>';
    	print_r($response);
    	echo '</br>';
    	echo '************ classify_response $response *********************';
    	echo '</br>';*/
    	
        /*if (!array_key_exists('answer', $response)) {
            return array($this->id => question_classified_response::no_response());
        }
        list($fraction) = $this->grade_response($response);
        if ($response['answer']) {
            return array($this->id => new question_classified_response(1,
                    get_string('true', 'qtype_simulationceis'), $fraction));
        } else {
            return array($this->id => new question_classified_response(0,
                    get_string('false', 'qtype_simulationceis'), $fraction));
        }*/
    }

    public function is_complete_response(array $response) {
    	/*echo '</br>';
    	echo '************ is_complete_response $response *********************';
    	echo '</br>';
    	print_r($response);
    	echo '</br>';
    	echo '************ is_complete_response $response *********************';
    	echo '</br>';*/
        return array_key_exists('answer', $response);
    }

    public function get_validation_error(array $response) {
    	/*echo '</br>';
    	echo '************ get_validation_error $response *********************';
    	echo '</br>';
    	print_r($response);
    	echo '</br>';
    	echo '************ get_validation_error $response *********************';
    	echo '</br>';*/
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseselectananswer', 'qtype_simulationceis');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
    	/*echo '</br>';
    	echo '************ is_same_response $prevresponse *********************';
    	echo '</br>';
    	print_r($prevresponse);
    	echo '</br>';
    	echo '************ is_same_response $prevresponse *********************';
    	echo '</br>';*/
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function grade_response(array $response) {
    	/*echo '</br>';
    	echo '************ grade_response $response *********************';
    	echo '</br>';
    	print_r($response);
    	echo '</br>';
    	echo '************ grade_response $response *********************';
    	echo '</br>';*/
    	/*
    	if (isset($state->responses['grade'])) {
            $gr = (float)$state->responses['grade'];
            $gr = $gr < 0 ? 0 : $gr;
            $gr = $gr > 1 ? 1 : $gr;
			$state->raw_grade = $gr * $question->maxgrade;
		} 
        // mark the state as graded
        $state->event = ($state->event == QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;
        return true;
    	*/
    	
        if ($this->rightanswer == true && $response['answer'] == true) {
            $fraction = 1;
        } else if ($this->rightanswer == false && $response['answer'] == false) {
            $fraction = 1;
        } else {
            $fraction = 0;
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $answerid = reset($args); // itemid is answer id.
            $response = $qa->get_last_qt_var('answer', '');
            echo 'check_file_access';
            print_r($response);
            return $options->feedback && (
                    ($answerid == $this->trueanswerid && $response) ||
                    ($answerid == $this->falseanswerid && $response !== ''));

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
