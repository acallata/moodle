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
 * Question type class for the true-false question type.
 *
 * @package    qtype
 * @subpackage simulationceis
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The true-false question type class.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationceis extends question_type {
    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();
        $context = $question->context;

    	if ($options = $DB->get_record('question_simulationceis', array('question' => $question->id))) {
            //$options->question = $question->id;
            $options->width = $question->flashwidth;
            $options->height = $question->flashheight;
            $options->flashfile = $question->flashfile;
            @$options->optionalfile = $question->optionalfile;
            $options->optionaldata = $question->optionaldata;
            if (!$DB->update_record('question_simulationceis', $options)) {
                $result->error = "Could not update quiz flash options! (id=$options->id)";
                return $result;
            }
        } else {
            $options = new stdClass();
            $options->question = $question->id;
            $options->width = $question->flashwidth;
            $options->height = $question->flashheight;
            $options->flashfile = $question->flashfile;
            @$options->optionalfile = $question->optionalfile;
            $options->optionaldata = $question->optionaldata;
            if (!$DB->insert_record('question_simulationceis', $options)) {
                $result->error = 'Could not insert quiz flash options!';
                return $result;
            }
        }
        return true;
    }

    /**
     * Loads the question type specific options for the question.
     */
    public function get_question_options($question) {
        global $DB, $OUTPUT;        
		// Get additional information from database
        // and attach it to the question object
        if (!$options = $DB->get_record('question_simulationceis', array('question' => $question->id))) {
        	echo $OUTPUT->notification('Error: Missing question options!');
            return false;
        }
        $question->flashwidth = $options->width;
        $question->flashheight = $options->height;
        $question->flashfile = $options->flashfile;
        @$question->optionalfile = $options->optionalfile;
        @$question->optionaldata = $options->optionaldata;

        return true;
    }
   
    protected function initialise_question_instance(question_definition $question, $questiondata) {
    	/*echo '-------------------------------';
    	echo '</br>';
    	print_r($question);
    	echo '</br>';
    	echo '</br>';
    	print_r($questiondata);
    	echo '</br>';
    	echo '-------------------------------';
*/
    	//require_js($CFG->wwwroot.'/question/type/simulationceis/js/flash_tag.js');
        //require_js($CFG->wwwroot.'/question/type/simulationceis/js/interface.js');
    	parent::initialise_question_instance($question, $questiondata);
    }
   
    
    public function save_question($question, $form) {
        global $USER, $DB, $OUTPUT;

        /*echo '-------------------------------';
    	echo '</br>';
    	print_r($question);
    	echo '</br>';
    	echo '-------------------------------';*/
        list($question->category) = explode(',', $form->category);
        $context = $this->get_context_by_category_id($question->category);

        // This default implementation is suitable for most
        // question types.

        // First, save the basic question itself
        $question->name = trim($form->name);
        $question->parent = isset($form->parent) ? $form->parent : 0;
        $question->length = $this->actual_number_of_questions($question);
        $question->penalty = isset($form->penalty) ? $form->penalty : 0;

        if (empty($form->questiontext['text'])) {
            $question->questiontext = '';
        } else {
            $question->questiontext = trim($form->questiontext['text']);;
        }
        $question->questiontextformat = !empty($form->questiontext['format']) ?
                $form->questiontext['format'] : 0;

        if (empty($form->generalfeedback['text'])) {
            $question->generalfeedback = '';
        } else {
            $question->generalfeedback = trim($form->generalfeedback['text']);
        }
        $question->generalfeedbackformat = !empty($form->generalfeedback['format']) ?
                $form->generalfeedback['format'] : 0;

        if (empty($question->name)) {
            $question->name = shorten_text(strip_tags($form->questiontext['text']), 15);
            if (empty($question->name)) {
                $question->name = '-';
            }
        }

        if ($question->penalty > 1 or $question->penalty < 0) {
            $question->errors['penalty'] = get_string('invalidpenalty', 'question');
        }

        if (isset($form->defaultmark)) {
            $question->defaultmark = $form->defaultmark;
        }

        // If the question is new, create it.
        if (empty($question->id)) {
            // Set the unique code
            $question->stamp = make_unique_id_code();
            $question->createdby = $USER->id;
            $question->timecreated = time();
            $question->id = $DB->insert_record('question', $question);
        }

        // Now, whether we are updating a existing question, or creating a new
        // one, we have to do the files processing and update the record.
        /// Question already exists, update.
        $question->modifiedby = $USER->id;
        $question->timemodified = time();

        if (!empty($question->questiontext) && !empty($form->questiontext['itemid'])) {
            $question->questiontext = file_save_draft_area_files($form->questiontext['itemid'],
                    $context->id, 'question', 'questiontext', (int)$question->id,
                    $this->fileoptions, $question->questiontext);
        }
        if (!empty($question->generalfeedback) && !empty($form->generalfeedback['itemid'])) {
            $question->generalfeedback = file_save_draft_area_files(
                    $form->generalfeedback['itemid'], $context->id,
                    'question', 'generalfeedback', (int)$question->id,
                    $this->fileoptions, $question->generalfeedback);
        }
        $DB->update_record('question', $question);

        // Now to save all the answers and type-specific options
        $form->id = $question->id;
        $form->qtype = $question->qtype;
        $form->category = $question->category;
        $form->questiontext = $question->questiontext;
        $form->questiontextformat = $question->questiontextformat;
        // current context
        $form->context = $context;

        $result = $this->save_question_options($form);

        if (!empty($result->error)) {
            print_error($result->error);
        }

        if (!empty($result->notice)) {
            notice($result->notice, "question.php?id=$question->id");
        }

        if (!empty($result->noticeyesno)) {
            throw new coding_exception(
                    '$result->noticeyesno no longer supported in save_question.');
        }

        // Give the question a unique version stamp determined by question_hash()
        $DB->set_field('question', 'version', question_hash($question),
                array('id' => $question->id));

        return $question;
    }
    
    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('question_simulationceis', array('question' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        return 0.5;
    }
    
    public function get_possible_responses($questiondata) {
    	echo '----------get_possible_responses ----------';
        echo '</br>';print_r($questiondata);echo '</br>';
        echo '----------get_possible_responses ----------';
        /*return array(
            $questiondata->id => array(
                0 => new question_possible_response(get_string('false', 'qtype_simulationceis'),
                        $questiondata->options->answers[
                        $questiondata->options->falseanswer]->fraction),
                1 => new question_possible_response(get_string('true', 'qtype_simulationceis'),
                        $questiondata->options->answers[
                        $questiondata->options->trueanswer]->fraction),
                null => question_possible_response::no_response()
            )
        );*/
    }
    
        /*************************** c—digo incorporado , code cpy from flash original **********************************/
	
	function delete_states($stateslist) {
        /// The default question type does not have any tables of its own
        // therefore there is nothing to delete

    	delete_records_select('question_flash_states', "stateid IN ($stateslist)");
        return true;
    }
    
	function grade_responses(&$question, &$state, $cmoptions) {
        // Only allow one attempt at the question
        //$state->penalty = 0;
		if (isset($state->responses['grade'])) {
            $gr = (float)$state->responses['grade'];
            $gr = $gr < 0 ? 0 : $gr;
            $gr = $gr > 1 ? 1 : $gr;
			$state->raw_grade = $gr * $question->maxgrade;
		} 
        // mark the state as graded
        $state->event = ($state->event == QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;
        return true;
    }
    
	function response_summary($question, $state, $length=80) {
        if (isset($question->options->answers[$state->answer])) {
            $responses = $question->options->answers[$state->answer]->answer;
        } else {
            $responses = '';
        }
        return $responses;
    }

    function get_all_responses(&$question, &$state) {
    	
    	$result = parent::get_all_responses($question, $state);
    	foreach ($result->responses as $res) {
    		if ($res->credit == 1) {
    			return $result;
    		}
    	}
        $r = new stdClass;
        $r->answer = '100%';
        $r->credit = 1;
        $result->responses[0] = $r;
        return $result;
    }
    
	function get_actual_response($question, $state) {
       if (!empty($state->responses)) {
           $responses[] = $state->responses[''];
       } else {
           $responses[] = '';
       }
       return $responses;
    }
    
    function restore_session_and_responses(&$question, &$state) {
        if (!$options = $DB->get_record('question_simulationceis_states', 'stateid', $state->id)) {
            return false;
        }
        $state->options->flashdata = $options->flashdata;
        $state->options->grade = $options->grade;
        $state->options->answer = $state->responses[''];
        $state->responses['flashdata'] = $options->flashdata;
        $state->responses['grade'] = $options->grade;
        return true;
    }
    
    function save_session_and_responses(&$question, &$state) {
        
        if (!$DB->set_field('question_states', 'answer', $state->responses[''], 'id', $state->id)) {
            return false;
        }
        $state->responses[''] = stripslashes($state->responses['']);
        if (!empty($state->responses['flashdata'])) {
        	$state->responses['flashdata'] = stripslashes($state->responses['flashdata']);
        }
        
		$options->stateid = $state->id;
        $options->flashdata = isset($state->responses['flashdata']) ? $state->responses['flashdata'] : '';
        $options->grade = isset($state->responses['grade']) ? $state->responses['grade'] : 0;
        $state->options = clone($options);
        // Only in this function we already know $state->id
        if ($options->id = $DB->get_field('question_simulationceis_states', 'id', 'stateid', $state->id)) {
            if (!$DB->update_record('question_simulationceis_states', $options)) {
                return false;
            }
        } else {
            if (!$options->id = $DB->insert_record('question_simulationceis_states', $options)) {
                return false;
            }
        }

        if (!empty($state->responses[''])) {
            if (!$answer = $DB->get_record('question_answers', 'question', $question->id, 'answer', $state->responses[''])) {
                $answer->question = $question->id;
                $answer->answer = $state->responses[''];
                $answer->fraction = $options->grade;
                $DB->insert_record('question_answers', $answer);
            } else {
                $answer->fraction = $options->grade;
                $DB->update_record('question_answers', $answer);
            }
        }

        return true;
    }
    
    
	function backup($bf,$preferences,$question,$level=6) {

        $status = true;

        // Output the flash question settings.
        $flashoptions = $DB->get_record('question_flash', 'question', $question);
        if ($flashoptions) {
            $status = fwrite ($bf,start_tag('FLASHOPTIONS',6,true));
            fwrite ($bf,full_tag('WIDTH',7,false,$flashoptions->width));
            fwrite ($bf,full_tag('HEIGHT',7,false,$flashoptions->height));
            fwrite ($bf,full_tag('OPTIONALFILE',7,false,$flashoptions->optionalfile));
            fwrite ($bf,full_tag('OPTIONALDATA',7,false,$flashoptions->optionaldata));
            $status = fwrite ($bf,end_tag('FLASHOPTIONS',6,true));
        }

        return $status;
    }
    
	function restore($old_question_id,$new_question_id,$info,$restore) {

        $status = true;

        //We have created every match_sub, now create the match
        $flash = new stdClass;
        $flash->question = $new_question_id;

        // Get options
        $flash->width = backup_todb($info['#']['FLASHOPTIONS']['0']['#']['WIDTH']['0']['#']);
        $flash->height = backup_todb($info['#']['FLASHOPTIONS']['0']['#']['HEIGHT']['0']['#']);
        $flash->optionalfile = backup_todb($info['#']['FLASHOPTIONS']['0']['#']['OPTIONALFILE']['0']['#']);
        $flash->optionaldata = backup_todb($info['#']['FLASHOPTIONS']['0']['#']['OPTIONALDATA']['0']['#']);

        //The structure is equal to the db, so insert the question_match_sub
        $newid = insert_record ('question_flash', $flash);

        if (!$newid) {
            $status = false;
        }

        return $status;
    }
    
    public function print_question_formulation_and_controls(&$question, &$state,
            $cmoptions, $options) {
        global $CFG;

        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->para = false;

        $qName = 'q'.$question->id;
        $readonly = $options->readonly ? '&qRO=1' : '';
        $adaptive = ($cmoptions->optionflags & QUESTION_ADAPTIVE) ?  '&qAM=1' : '';
        $fillcorrect = $options->correct_responses ? '&qFC=1' : '';
        if ($options->responses) {
        	$flashdata = !empty($state->options->flashdata) ? addslashes_js('&flData='.$state->options->flashdata) : '';
        }
		$description = !empty($state->options->answer) ? addslashes_js('&qDesc='.$state->options->answer) : '';
		$grade = '&qGr='.$state->raw_grade;

        $width  = $question->flashwidth;
        $height = $question->flashheight;
        $optionalfile = !empty($question->optionalfile) ? '&optFile='.get_file_url("{$cmoptions->course}/FlashQuestions/{$question->optionalfile}") : '';
        $optionaldata = !empty($question->optionaldata) ? addslashes_js('&optData='.$question->optionaldata) : '';
		
        // Print question formulation
        $questiontext = format_text($question->questiontext,
                         $question->questiontextformat,
                         $formatoptions, $cmoptions->course);
        $image = get_question_image($question, $cmoptions->course);
 		include("$CFG->dirroot/question/type/simulationceis/display.html");
       
    }
 
    /*************************** c—digo incorporado , code cpy from flash original **********************************/
    
}
