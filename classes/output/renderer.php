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
 * The parsonsproblem question renderer class is defined here.
 *
 * @package     qtype_parsonsproblem
 * @copyright   2023 Matías Marchant <matias.marchant@sansano.usm.cl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for parsonsproblem questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class qtype_parsonsproblem_renderer extends qtype_renderer {

    /**
     * Generates the display of the formulation part of the question. This is the
     * area that contains the quetsion text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        if ($options->readonly) {
            $output = $this->students_answer_render($question, $qa->get_response_summary(), $qa->get_right_answer_summary());
            return parent::formulation_and_controls($qa, $options) . $output;
        }
        $output = "";
        $output .= html_writer::empty_tag('div', array('class' => 'parsons sortable-container'));
        $output .= html_writer::start_div('parsons sortable-column-left', array('id' => 'column' . $question->id . '0'));
        $order = $question->get_order_indentationless($qa);
        $distractors = $question->get_distractors();
        $order = array_merge($order, $distractors);
        shuffle($order);
        $rowamount = 0;
        foreach ($order as $codefragment) {
            $inputname = 'line' . $rowamount . '_' . $question->id;
            $inputattributes = array(
                'id' => $inputname,
                'class' => 'parsons sortable-item',
            );
            if ($question->choicedelimiterexists() && strpos($codefragment, $question->choicedelimiter) !== false) {
                $inputattributes['class'] = 'parsons sortable-choice-parent';
                $output .= html_writer::start_div('', $inputattributes);
                $codefragment = ltrim($codefragment, $question->choicedelimiter);
                $codefragment = rtrim($codefragment, $question->choicedelimiterr);
                $choices = explode($question->choicedelimiterm, $codefragment);
                $choicecounter = 0;
                foreach ($choices as $choice) {
                    $inputattributeschoice = array(
                        'id' => $inputname . '_' . $choicecounter,
                        'class' => 'sortable-choice',
                    );
                    $output .= html_writer::tag('div', $question->trim_string_min_left_whitespaces($choice), $inputattributeschoice);
                    $choicecounter++;
                }

                $output .= html_writer::end_div();
            } else {
                $output .= html_writer::tag('div', $question->trim_string_min_left_whitespaces($codefragment), $inputattributes);
            }
            $rowamount++;
        }
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('parsons sortable-column-right', array('id' => 'column' . $question->id . '1'));
        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('div');

        $responsename = $question->get_response_fieldname();
        $answerid = $qa->get_qt_field_name($responsename);
        $initialvalue = "";
        $output .= html_writer::empty_tag(
            'input', array('type' => 'hidden',
            'name' => $answerid,
            'id' => $answerid,
            'value' => $initialvalue,
            )
            );

        $this->page->requires->js_call_amd(
            'qtype_parsonsproblem/draganddropcodefragments',
            'init',
            array($question->id, $answerid)
        );
        return parent::formulation_and_controls($qa, $options) . $output;
    }

    /**
     * Generates an automatic description of the correct response to this question.
     * Not all question types can do this. If it is not possible, this method
     * should just return an empty string.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $output = "";
        $output .= html_writer::tag('p', get_string('correctorder', 'qtype_parsonsproblem'));
        $output .= html_writer::start_tag('ol');
        $rightanswer = $qa->get_right_answer_summary();
        $rightanswer = $question->codedelimiterexists() ?
            explode($question->codedelimiter, $rightanswer)
            :
            explode("|/", $rightanswer);
        foreach ($rightanswer as $lineofcode) {
            $output .= html_writer::tag('li', $lineofcode, array('class' => 'parsons-feedback'));
        }
        $output .= html_writer::end_tag('ol');
        return parent::correct_response($qa) . html_writer::empty_tag('br') . $output;
    }

    public function students_answer_render($question, $responsesummary, $rightanswer) {
        $output = "";
        $responsesummary = explode("|/", $responsesummary);
        $rightanswer = $question->codedelimiterexists() ?
            explode($question->codedelimiter, $rightanswer)
            :
            explode("|/", $rightanswer);
        $correctlines = $this->get_correct_lines_array($responsesummary, $rightanswer);
        $output .= html_writer::empty_tag('div', array('class' => 'parsons sortable-container'));
        $output .= html_writer::start_div('parsons sortable-column-feedback');
        foreach ($responsesummary as $index => $answer) {
            $indentations = strlen($answer) - strlen(ltrim($answer));
            if (isset($correctlines[$index])) {
                $status = $correctlines[$index] ? (' correct') : (' incorrect');
            } else {
                $status = (' incorrect');
            }
            $inputattributes = array(
                'class' => 'parsons sortable-item parsons-feedback' . $status,
                'style' => 'margin-left:' . strval(($indentations / 4) * 50) . 'px',
            );
            $output .= html_writer::tag('div', $question->trim_string_min_left_whitespaces($answer), $inputattributes);
        }
        $output .= html_writer::end_div() . html_writer::end_tag('div');
        return $output;
    }

    public function get_correct_lines_array($responsesummary, $rightanswer) {
        $correctlinesarray = array();
        foreach ($responsesummary as $index => $lineofcode) {
            if (isset($rightanswer[$index])) {
                if ($lineofcode == $rightanswer[$index]) {
                    $correctlinesarray[$index] = true;
                } else {
                    $correctlinesarray[$index] = false;
                }
            }
        }
        return $correctlinesarray;
    }

}
