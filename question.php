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
 * Question definition class for parsonsproblem.
 *
 * @package     qtype_parsonsproblem
 * @copyright   2023 Matías Marchant <matias.marchant@sansano.usm.cl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

/**
 * Class that represents a parsonsproblem question.
 */
class qtype_parsonsproblem_question extends question_graded_automatically {


    /** @var array dragItem order with indentation spaces */
    public $order = null;

    /** @var array of question_answer. */
    public $answers;

    /**
     * its a whole number, it's only called fraction because it is referred to that in core
     * code
     * @var int
     */
    public $fraction;


    /**
     * Returns data to be included in the form submission.
     *
     * @return array|string.
     */
    public function get_expected_data() {
        $name = $this->get_response_fieldname();
        $data = array($name => PARAM_TEXT);
        return $data;
    }

    /**
     * Returns the data that would need to be submitted to get a correct answer.
     *
     * @return array|null Null if it is not possible to compute a correct response.
     */
    public function get_correct_response() {
        $str = implode(empty($this->codedelimiter) ? '|/' : $this->codedelimiter, array_map(function($c) {
            return $c->answer;
        }, $this->get_parsonsproblem_answers()));
        return array($this->get_response_fieldname() => $str);
    }

    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete. That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response)
    {
        return true;
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed. This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $old the responses previously recorded for this question,
     *      as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $new the new responses, in the same format.
     * @return bool whether the two sets of responses are the same - that is
     *      whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse)
    {
        return null;
    }

    /**
     * Produce a plain text summary of a response.
     *
     * @param array $response a response, as might be passed to {@link grade_response()}.
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response)
    {
        if (isset($response[$this->get_response_fieldname()])) {
            return $response[$this->get_response_fieldname()];
        } else {
            return null;
        }
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     * @param array $response
     * @return string the message
     */
    public function get_validation_error(array $response)
    {
        return null;
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and get_max_fraction(), and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return array (float, integer) the fraction, and the state.
     */
    public function grade_response(array $response)
    {
        $counter = 0;
        $right = 0;
        $responsefieldname = $this->get_response_fieldname();
        $studentanswer = explode('|/', $response[$responsefieldname]);
        $correctanswer = explode(empty($this->codedelimiter) ? '|/' : $this->codedelimiter, $this->get_correct_response()[$responsefieldname]);
        foreach ($studentanswer as $index => $lineofcode) {
            if (isset($correctanswer[$index])) {
                if ($lineofcode == $correctanswer[$index]) { $right++; }
            }
            $counter++;
        }

        $this->fraction = $right / max($counter, count($correctanswer));
        $grade = array($this->fraction, question_state::graded_state_for_fraction($this->fraction));
        return $grade;
    }

    /**
     * Start a new attempt at this question, storing any information that will
     * be needed later in the step.
     *
     * This is where the question can do any initialisation required on a
     * per-attempt basis. For example, this is where the multiple choice
     * question type randomly shuffles the choices (if that option is set).
     *
     * Any information about how the question has been set up for this attempt
     * should be stored in the $step, by calling $step->set_qt_var(...).
     *
     * @param question_attempt_step The first step of the {@link question_attempt}
     *      being started. Can be used to store state.
     * @param int $varant which variant of this question to start. Will be between
     *      1 and {@link get_num_variants()} inclusive.
     */
    public function start_attempt(question_attempt_step $step, $variant)
    {
        if($this->codedelimiterexists()) {
            $order = explode($this->codedelimiter , $this->code);
            $order = $this->shuffle_visually_paired($order);
            shuffle($order);
            $step->set_qt_var('_order', implode($this->codedelimiter, $order));
        } else {
            $order = preg_split("/\\r\\n|\\r|\\n/", $this->code);
            $order = $this->shuffle_visually_paired($order);
            shuffle($order);
            $step->set_qt_var('_order', implode('|/', $order));
        }
    }

    protected function init_order(question_attempt $qa)
    {
        if (is_null($this->order)) {
            $this->order = explode($this->codedelimiterexists() ? $this->codedelimiter : '|/', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    public function get_order(question_attempt $qa)
    {
        $this->init_order($qa);
        return $this->order;
    }

    public function get_order_indentationless(question_attempt $qa)
    {
        return $this->trim_array_min_left_whitespaces($this->get_order($qa));
    }

    public function trim_array_min_left_whitespaces($codeFragments) {
        return array_map(function($codeFragment) {
            return $this->trim_string_min_left_whitespaces($codeFragment);
        }, $codeFragments);
    }

    public function trim_string_min_left_whitespaces($codeFragment) {
        if (preg_match("/\\r\\n|\\r|\\n/", $codeFragment)) {
            $codeFragmentBlock = preg_split("/\\r\\n|\\r|\\n/", $codeFragment);
            $counter = 0;
            $aux_array = array();
            foreach ($codeFragmentBlock as $line) {
                foreach (mb_str_split($line) as $char) {
                    if ($char == ' ') {
                        if (isset($aux_array[$counter])) {
                            $aux_array[$counter]++;
                        } else {
                            $aux_array[$counter] = 1;
                        }
                    } else {
                        break;
                    }
                }
                $counter++;
            }
            $min = min($aux_array);
            foreach ($codeFragmentBlock as $index => $line) {
                $codeFragmentBlock[$index] = substr($line, $min);
            }
            return implode(PHP_EOL, $codeFragmentBlock);
        }
        return ltrim($codeFragment);
    }

    /**
     * Loads from DB and returns array of answers objects
     *
     * @return array of objects
     */
    public function get_parsonsproblem_answers() {
        global $DB;
        if ($this->answers === null) {
            $this->answers = $DB->get_records('question_answers', array('question' => $this->id), 'id ASC');
            if ($this->answers) {
                foreach ($this->answers as $answerid => $answer) {
                    $this->answers[$answerid] = $answer;
                }
            } else {
                $this->answers = array();
            }
        }
        return $this->answers;
    }

    /**
     * Checks whether the user is allowed to be served a particular file.
     *
     * @param question_attempt $qa The question attempt being displayed.
     * @param question_display_options $options The options that control display of the question.
     * @param string $component The name of the component we are serving files for.
     * @param string $filearea The name of the file area.
     * @param array $args the Remaining bits of the file path.
     * @param bool $forcedownload Whether the user must be forced to download the file.
     * @return bool True if the user can access this file.
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    /**
     * Returns response mform field name
     *
     * @return string
     */
    public function get_response_fieldname() {
        return 'parsons-response_'.$this->id;
    }

    /**
     * Returns if choice delimiter was set when creating the question
     *
     * @return bool
     */
    public function choicedelimiterexists() {
        return !empty($this->choicedelimiter) && !empty($this->choicedelimiterm) && !empty($this->choicedelimiterr);
    }

    /**
     * Returns if code delimiter was set when creating the question
     *
     * @return bool
     */
    public function codedelimiterexists() {
        return !empty($this->codedelimiter);
    }

    public function distractorsdelimiterexists() {
        return !empty($this->distractorsdelimiter);
    }

    public function shuffle_visually_paired($order) {
        if($this->choicedelimiterexists()) {
            foreach ($order as $index => $codeFragment) {
                if (strpos($codeFragment, $this->choicedelimiter) !== false) {
                    // Trim left and right choice delimiter
                    $ltrimmed = ltrim(ltrim($codeFragment), $this->choicedelimiter);
                    $randltrimmed = rtrim($ltrimmed, $this->choicedelimiterr);
                    $unshuffled = explode($this->choicedelimiterm, $randltrimmed);
                    shuffle($unshuffled);
                    $withdelimiters = $this->choicedelimiter . implode($this->choicedelimiterm, $unshuffled) . $this->choicedelimiterr;
                    $order[$index] = $withdelimiters;
                }
            }
        }
        return $order;
    }

    public function get_distractors() {
        $distractors = array();
        if($this->distractorsdelimiterexists()) {
            $distractors = explode($this->distractorsdelimiter,  $this->distractors);
        }
        return $distractors;
    }
}
