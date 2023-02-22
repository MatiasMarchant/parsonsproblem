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
        global $PAGE;
        $question = $qa->get_question();
        $output = "";
        $output .= html_writer::empty_tag('div', array('class' => 'parsons sortable-container'));
        $output .= html_writer::start_div('parsons sortable-column-left', array('id' => 'column' . $question->id . '0'));
        $order = $question->get_order_indentationless($qa);
        $rowAmount = 0;
        foreach ($order as $index => $codefragment) {
            $inputname = 'line' . $rowAmount . '_' . $question->id;
            $inputattributes = array(
                'id' => $inputname,
                'class' => 'parsons sortable-item',
            );
            if (!empty($question->choicedelimiter) && strpos($codefragment, $question->choicedelimiter)) {
                $inputattributes['class'] = 'parsons sortable-choice-parent';
                $output .= html_writer::start_div('', $inputattributes);

                // Hacer html writer de la weaita
                $choices = explode($question->choicedelimiter, $codefragment);
                $choicecounter = 0;
                foreach ($choices as $choice) {
                    $inputattributeschoice = array(
                        'id' => $inputname . '_' . $choicecounter,
                        'class' => 'sortable-choice',
                    );
                    $output .= html_writer::tag('div', $choice, $inputattributeschoice);
                    $choicecounter++;
                }

                $output .= html_writer::end_div();
            } else {
                $output .= html_writer::tag('div', $codefragment, $inputattributes);
            }
            $rowAmount++;
        }
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('parsons sortable-column-right', array('id' => 'column' . $question->id . '1'));
        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('div');

        // FALTA PRINTEAR DISTRACTORES!! SI ESQ HAY

        $responsename = $question->get_response_fieldname();
        $answerid = $qa->get_qt_field_name($responsename);
        $initialValue = "";
        $output .= html_writer::empty_tag(
            'input', array('type' => 'hidden',
            'name' => $answerid,
            'id' => $answerid,
            'value' => $initialValue,
            )
            );

        $PAGE->requires->js_call_amd('qtype_parsonsproblem/draganddropcodefragments', 'init', array($question->id, $answerid));
        return parent::formulation_and_controls($qa, $options) . $output;
    }

    /**
     * Generate the specific feedback. This is feedback that varies according to
     * the response the student gave. This method is only called if the display options
     * allow this to be shown.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function specific_feedback(question_attempt $qa) {
        return parent::specific_feedback($qa);
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
        return parent::correct_response($qa);
    }

}
