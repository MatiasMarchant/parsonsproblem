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
 * Plugin strings are defined here.
 *
 * @package     qtype_parsonsproblem
 * @category    string
 * @copyright   2023 Matías Marchant <matias.marchant@sansano.usm.cl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Parsons Problem question type.';
$string['pluginnamesummary'] = 'A question type where the student has to select, order, and indent code fragments.';
$string['pluginname_link'] = 'question/type/parsonsproblem';
$string['pluginnameadding'] = 'Adding a Parsons Problem question.';
$string['pluginnameediting'] = 'Editing a Parsons Problem question.';
$string['pluginname_help'] = 'Create a Parsons Problem question.';
$string['formcodefield'] = 'Insert the code to be fragmented into a Parsons Problem question.';
$string['formcodedelimiter'] = 'Insert code delimiter used to separate each code fragment, if not-specified/empty the default will be a new line.';
$string['choicedelimiterheader'] = 'Visually paired distractors delimiter';
$string['choicedelimiter'] =  'Insert delimiters used to create a visually paired distractor, the first choice will always be the correct one.';
$string['choicedelimiter_help'] = 'For example if you would input ",", the code "print("Hello World"),print Hello World" would be a visually paired distractor where the first choice print("Hello World") is the correct answer.';
$string['distractorsheader'] = 'Distractors';
$string['distractors'] = 'Insert distractors separated by your choice of distractors delimiter.';
$string['distractors_help'] = 'If empty, no distractor code fragment will be added';
$string['formdistractorsdelimiter'] = 'Insert distractors delimiter.';
