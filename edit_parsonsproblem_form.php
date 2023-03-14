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
 * The editing form for parsonsproblem question type is defined here.
 *
 * @package     qtype_parsonsproblem
 * @copyright   2023 Matías Marchant <matias.marchant@sansano.usm.cl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * parsonsproblem question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_parsonsproblem_edit_form extends question_edit_form {

    /**
     * Add any question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform)
    {
        $textareaattributes = array (
            'rows' => '10',
            'style' => 'display:flex;flex:1',
        );
        $textattributes = array (
            'size' => '70',
            'rows' => '1',
        );

        $mform->addElement('static', 'must consider',
                get_string('mustconsider', 'qtype_parsonsproblem'),
                get_string('nonallowedcodedelimiter', 'qtype_parsonsproblem'));

        // Code & Code delimiter
        $mform->addElement('textarea', 'code',
                get_string('formcodefield', 'qtype_parsonsproblem'), $textareaattributes);
        $mform->addElement('text', 'codedelimiter',
                get_string('formcodedelimiter', 'qtype_parsonsproblem'), $textattributes);
        $mform->setType('codedelimiter', PARAM_RAW);

        // Visually paired distractors
        $mform->addElement('header', 'choicedelimiterheader',
                get_string('choicedelimiterheader', 'qtype_parsonsproblem'));
        $mform->addElement('text', 'choicedelimiter',
                get_string('choicedelimiter', 'qtype_parsonsproblem'), $textattributes);
        $mform->addHelpButton('choicedelimiter', 'choicedelimiter', 'qtype_parsonsproblem');
        $mform->setType('choicedelimiter', PARAM_RAW);

        $mform->addElement('text', 'choicedelimiterm',
                get_string('choicedelimiterm', 'qtype_parsonsproblem'), $textattributes);
        $mform->addHelpButton('choicedelimiterm', 'choicedelimiterm', 'qtype_parsonsproblem');
        $mform->setType('choicedelimiterm', PARAM_RAW);

        $mform->addElement('text', 'choicedelimiterr',
                get_string('choicedelimiterr', 'qtype_parsonsproblem'), $textattributes);
        $mform->addHelpButton('choicedelimiterr', 'choicedelimiterr', 'qtype_parsonsproblem');
        $mform->setType('choicedelimiterr', PARAM_RAW);


        // Distractors
        $mform->addElement('header', 'distractorsheader',
                get_string('distractorsheader', 'qtype_parsonsproblem'));
        $mform->addElement('text', 'distractorsdelimiter',
                get_string('formdistractorsdelimiter', 'qtype_parsonsproblem'), $textattributes);
        $mform->setType('distractorsdelimiter', PARAM_RAW);
        $mform->addElement('text', 'distractors',
                get_string('distractors', 'qtype_parsonsproblem'), $textattributes);
        $mform->addHelpButton('distractors', 'distractors', 'qtype_parsonsproblem');
        $mform->setType('distractors', PARAM_RAW);
    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'parsonsproblem';
    }
}
