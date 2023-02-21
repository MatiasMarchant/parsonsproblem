<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Question type class for parsonsproblem is defined here.
 *
 * @package     qtype_parsonsproblem
 * @copyright   2023 Matías Marchant <matias.marchant@sansano.usm.cl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

/**
 * Class that represents a parsonsproblem question type.
 *
 * The class loads, saves and deletes questions of the type parsonsproblem
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_parsonsproblem extends question_type {

    // Override functions as necessary from the parent class located at
    // /question/type/questiontype.php.

    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields()
    {
        return array('qtype_parsonsproblem', 'code', 'codedelimiter', 'choicedelimiter', 'distractors', 'distractorsdelimiter');
    }

    /**
     * Saves question-type specific options
     *
     * This is called by {@link save_question()} to save the question-type specific data
     * @return object $result->error or $result->notice
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     */
    public function save_question_options($question) {
        parent::save_question_options($question);
        $this->save_question_answers($question);
    }

    /**
     * Save the answers, with any extra data.
     *
     * Questions that use answers will call it from {@link save_question_options()}.
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     * @return object $result->error or $result->notice
     */
    public function save_question_answers($question)
    {
        global $DB;

        // As this function gets called when saving a new question and when editing an existing question
        // one must consider both cases
        $context = $question->context;
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');

        $unprocessed = $question->code;
        if(empty($question->codedelimiter)) {
            $processed = preg_split("/\\r\\n|\\r|\\n/", $unprocessed);
        } else {
            $processed = explode($question->codedelimiter , $unprocessed);
        }

        if(!empty($question->choicedelimiter)) {
            $processed = $this->remove_choices($processed, $question->choicedelimiter);
        }

        // Insert all the new answers
        foreach ($processed as $fragment) {
            // Update an existing answer if possible.
            if($answer = array_shift($oldanswers)) {
                $answer->question = $question->id;
                $answer->answer = $fragment;
                $answer->feedback = '';
                $answer->fraction = 1.0;
                $DB->update_record('question_answers', $answer);
            } else {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $fragment;
                $answer->feedback = '';
                $answer->fraction = 1.0;
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

    }

    /**
     * Cleans final answer removing incorrect choices in every fragment of code
     * when using visually paired choices
     * @param array $processed  Each fragment of code including incorrect choices
     * @param string $choicedelimiter  Separator string that separates each choice
     *
     * @return array $codefragments  Each correct fragment of code
     */
    public function remove_choices($processed, $choicedelimiter)
    {
        $codefragments = array();
        foreach ($processed as $possiblefragment) {
            $fragment = explode($choicedelimiter, $possiblefragment);
            array_push($codefragments, array_shift($fragment));
        }
        return $codefragments;
    }
}
