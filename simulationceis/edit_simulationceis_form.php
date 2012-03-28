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
 * Defines the editing form for the true-false question type.
 *
 * @package    qtype
 * @subpackage simulationceis
 * @copyright  2012 Victor_Aravena Alvaro_Callata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/question/type/edit_question_form.php');


/**
 * True-false question editing form definition.
 *
 * @copyright  2012 Victor_Aravena Alvaro_Callata
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationceis_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
protected function definition() {
        global $COURSE, $CFG, $DB;

        $qtype = $this->qtype();
        $langfile = "qtype_$qtype";

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            // Adding question
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                    array('contexts' => $this->contexts->having_cap('moodle/question:add')));
        } else if (!($this->question->formoptions->canmove ||
                $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                    array('contexts' => array($this->categorycontext)));
        } else if ($this->question->formoptions->movecontext) {
            // Moving question to another context.
            $mform->addElement('questioncategory', 'categorymoveto',
                    get_string('category', 'question'),
                    array('contexts' => $this->contexts->having_cap('moodle/question:add')));

        } else {
            // Editing question with permission to move from category or save as new q
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                    get_string('categorycurrent', 'question'),
                    array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                    $this->question->formoptions->cansaveasnew) {
                //not move only form
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                        get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp',
                    get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto',
                    get_string('categorymoveto', 'question'),
                    array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                    $this->question->formoptions->cansaveasnew) {
                //not move only form
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        $mform->addElement('text', 'name', get_string('questionname', 'question'),
                array('size' => 50));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

/*** Texto de la pregunta y Retroalimentaci—n ***/
        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),
                array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
                array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
/*** Texto de la pregunta y Retroalimentaci—n ***/

        $flashFiles = $CFG->dirroot.'/filesflash';
        //$flashFiles = $CFG->dataroot.'/FlashQuestions';
        make_upload_directory($flashFiles);
        $coursefiles = get_directory_list($flashFiles, $CFG->moddata);
	    foreach ($coursefiles as $filename) {
	            $filesFlash["$filename"] = $filename;
		 }
		 if (empty($filesFlash)) {
	        	$mform->addElement('static', 'flashfile', get_string('flashmovie', 'qtype_simulationceis'), get_string('noflashmovie', 'qtype_simulationceis'));
		 } else {
	            $mform->addElement('select', 'flashfile', get_string('flashmovie', 'qtype_simulationceis'), array(''=>get_string('none')) + $filesFlash);
	     }
	 
	    //$mform->setHelpButton('optionalfile', array('optionalfile', get_string('optionalfile', 'qtype_simulationceis'), 'qtype_simulationceis'));
	    $mform->setAdvanced('optionalfile');
        $mform->addRule('image', null, 'required', null, 'client');
        

        $mform->addElement('static', 'warning', '', utf8_encode(get_string('flashwarning', 'qtype_simulationceis')));
        //$mform->setHelpButton('warning', array('flashinterface', get_string('interface', 'qtype_simulationceis'), 'qtype_simulationceis'));

        $mform->addElement('text', 'flashwidth', get_string('flashwidth', 'qtype_simulationceis'),
                array('size' => 4));
        $mform->setType('flashwidth', PARAM_INT);
        $mform->setDefault('flashwidth', 640);
        $mform->addRule('flashwidth', null, 'required', null, 'client');

        $mform->addElement('text', 'flashheight', get_string('flashheight', 'qtype_simulationceis'),
                array('size' => 4));
        $mform->setType('flashheight', PARAM_INT);
        $mform->setDefault('flashheight', 480);
        $mform->addRule('flashheight', null, 'required', null, 'client');

        $mform->addElement('textarea', 'optionaldata', get_string('optionaldata', 'qtype_simulationceis'), 'wrap="virtual" rows="10" cols="45"');
        //$mform->setHelpButton('optionaldata', array('optionaldata', get_string('optionaldata', 'qtype_simulationceis'), 'qtype_simulationceis'));
        $mform->setAdvanced('optionaldata');

        $mform->addElement('text', 'defaultgrade', get_string('defaultgrade', 'quiz'),
        array('size' => 3));
        $mform->setType('defaultgrade', PARAM_INT);
        $mform->addRule('defaultgrade', null, 'required', null, 'client');
        $mform->setDefault('defaultgrade', 1);

        $mform->addElement('text', 'penalty', utf8_encode(get_string('penaltyfactor', 'qtype_simulationceis')),
        array('size' => 3));
        $mform->setType('penalty', PARAM_NUMBER);
        $mform->addRule('penalty', null, 'required', null, 'client');
        //$mform->setHelpButton('penalty', array('penalty', get_string('penalty', 'quiz'), 'quiz'));
        $mform->setDefault('penalty', 0.1);        
/*******************************************************************************************************/

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        /*if (!empty($CFG->usetags)) {
            $mform->addElement('header', 'tagsheader', get_string('tags'));
            $mform->addElement('tags', 'tags', get_string('tags'));
        }*/

        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader',
                    get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname($DB->get_record(
                        'user', array('id' => $this->question->createdby)));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'),
                     get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname($DB->get_record(
                        'user', array('id' => $this->question->modifiedby)));
                $mform->addElement('static', 'modified', get_string('modified', 'question'),
                        get_string('byandon', 'question', $a));
            }
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'movecontext');
        $mform->setType('movecontext', PARAM_BOOL);

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $buttonarray = array();
        if (!empty($this->question->id)) {
            // Editing / moving question
            if ($this->question->formoptions->movecontext) {
                $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                        get_string('moveq', 'question'));
            } else if ($this->question->formoptions->canedit ||
                    $this->question->formoptions->canmove ||
                    $this->question->formoptions->movecontext) {
                $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                        get_string('savechanges'));
            }
            if ($this->question->formoptions->cansaveasnew) {
                $buttonarray[] = $mform->createElement('submit', 'makecopy',
                        get_string('makecopy', 'question'));
            }
            $buttonarray[] = $mform->createElement('cancel');
        } else {
            // Adding new question
            $buttonarray[] = $mform->createElement('submit', 'submitbutton',
                    get_string('savechanges'));
            $buttonarray[] = $mform->createElement('cancel');
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if ($this->question->formoptions->movecontext) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar'));
        } else if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        /*echo '****** data_preprocessing ******';
        echo '</br>';print_r($question);echo '</br>';
        echo '****** data_preprocessing ******';

        if (!empty($question->options->trueanswer)) {
            $trueanswer = $question->options->answers[$question->options->trueanswer];
            $question->correctanswer = ($trueanswer->fraction != 0);

            $draftid = file_get_submitted_draft_itemid('trueanswer');
            $answerid = $question->options->trueanswer;

            $question->feedbacktrue = array();
            $question->feedbacktrue['format'] = $trueanswer->feedbackformat;
            $question->feedbacktrue['text'] = file_prepare_draft_area(
                $draftid,             // draftid
                $this->context->id,   // context
                'question',           // component
                'answerfeedback',     // filarea
                !empty($answerid) ? (int) $answerid : null, // itemid
                $this->fileoptions,   // options
                $trueanswer->feedback // text
            );
            $question->feedbacktrue['itemid'] = $draftid;
        }

        if (!empty($question->options->falseanswer)) {
            $falseanswer = $question->options->answers[$question->options->falseanswer];

            $draftid = file_get_submitted_draft_itemid('falseanswer');
            $answerid = $question->options->falseanswer;

            $question->feedbackfalse = array();
            $question->feedbackfalse['format'] = $falseanswer->feedbackformat;
            $question->feedbackfalse['text'] = file_prepare_draft_area(
                $draftid,              // draftid
                $this->context->id,    // context
                'question',            // component
                'answerfeedback',      // filarea
                !empty($answerid) ? (int) $answerid : null, // itemid
                $this->fileoptions,    // options
                $falseanswer->feedback // text
            );
            $question->feedbackfalse['itemid'] = $draftid;
        }*/

        return $question;
    }

    public function qtype() {
        return 'simulationceis';
    }
}