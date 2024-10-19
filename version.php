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
 * Certifications plugin version info.
 *
 * @package    tool_certify
 * @copyright  2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var stdClass $plugin */

$plugin->version   = 2024091100;
$plugin->requires  = 2023100902.00; // 4.3.2
$plugin->component = 'tool_certify';
$plugin->release   = 'v3.3.0';
$plugin->supported = [403, 403];

$plugin->dependencies = [
    'local_openlms' => 2024071401,
    'enrol_programs' => 2024080300,
];
