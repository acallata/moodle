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
 * True-false question renderer class.
 *
 * @package    qtype
 * @subpackage simulationceis
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/question/type/simulationceis/lib/File.php');
require_once($CFG->dirroot.'/question/type/simulationceis/lib/ArrayMarker.php');
/**
 * Generates the output for true-false questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationceis_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        global $CFG, $DB;
        $question = $qa->get_question();
        $optionsFlash = $DB->get_record('question_simulationceis', array('question' => $question->id));//Consultar al registro de la tabla simulacion

        $qName = 'q'.$question->id;
        $width  = $optionsFlash->width;
        $height = $optionsFlash->height;        
        $pathFlash = $CFG->wwwroot.'/filesflash/'.$optionsFlash->flashfile;

        $questiontext = format_text($question->questiontext,
                         $question->questiontextformat,
                         $formatoptions, $cmoptions->course);


        require_js($CFG->wwwroot.'/question/type/simulationceis/js/flash_tag.js');
        //require_js($CFG->wwwroot.'/question/type/simulationceis/js/interface.js');

        $fileHTML = new File($CFG->dirroot.'/question/type/simulationceis/lib/display.html');
        $myArrayMarker = new ArrayMarker();
        $Template = $fileHTML->read();
        $arrayContenido['DISPLAY'] = $myArrayMarker->getSubpart($Template,'###DISPLAY###');
        
        $markers['###QUESTION_TEXT###'] = $questiontext;
        $markers['###QUESTION_ID###'] = $question->id;
        $markers['###QNAME###'] = $qName;
        //$markers['###INCORRECT###'] = print_string('incorrect', 'quiz');
        //$markers['###PARTIAL_CORRECT###'] = print_string('partiallycorrect', 'quiz');
        //$markers['###CORRECT###'] = print_string('correct', 'quiz');
        $markers['###PATH_FLASH###'] = $pathFlash;
        $markers['###WIDTH###'] = $width;
        $markers['###HEIGHT###'] = $height;
        
        $result = $myArrayMarker->substituteMarkerArray($arrayContenido['DISPLAY'], $markers);
        
		//require_once($CFG->dirroot . '/question/type/edit_question_form.php');
        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
    	$question = $qa->get_question();
    	//echo '</br>';
    	//echo '------------ specific_feedback -------------------';
    	//echo '</br>';
    	//print_r($question);
    	//echo '</br>';
    	//echo '------------ specific_feedback -------------------';
    	//echo '</br>';
        /*
        $response = $qa->get_last_qt_var('answer', '');

        if ($response) {
            return $question->format_text($question->truefeedback, $question->truefeedbackformat,
                    $qa, 'question', 'answerfeedback', $question->trueanswerid);
        } else if ($response !== '') {
            return $question->format_text($question->falsefeedback, $question->falsefeedbackformat,
                    $qa, 'question', 'answerfeedback', $question->falseanswerid);
        }*/
    }

    public function correct_response(question_attempt $qa) {
    	$question = $qa->get_question();
    	//echo '</br>';
    	//echo '------------ correct_response -------------------';
    	//echo '</br>';
    	//print_r($question);
    	//echo '</br>';
    	//echo '------------ correct_response -------------------';
    	//echo '</br>';
        /*$question = $qa->get_question();

        if ($question->rightanswer) {
            return get_string('correctanswertrue', 'qtype_simulationceis');
        } else {
            return get_string('correctanswerfalse', 'qtype_simulationceis');
        }*/
    }
}
