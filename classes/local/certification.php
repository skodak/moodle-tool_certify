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

namespace tool_certify\local;

use stdClass;
use enrol_programs\local\course_reset;

/**
 * Certification helper.
 *
 * @package    tool_certify
 * @copyright  2023 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class certification {
    /** @var string relative date disabling flag */
    public const SINCE_NEVER = 'never';
    /** @var string relative to certified date */
    public const SINCE_CERTIFIED = 'certified';
    /** @var string relative to window start date */
    public const SINCE_WINDOWSTART = 'windowstart';
    /** @var string relative to window due date */
    public const SINCE_WINDOWDUE = 'windowdue';
    /** @var string relative to window end date */
    public const SINCE_WINDOWEND = 'windowend';

    /**
     * Options for editing of certification descriptions.
     *
     * @param int $contextid
     * @return array
     */
    public static function get_description_editor_options(int $contextid): array {
        $context = \context::instance_by_id($contextid);
        return ['maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => get_site()->maxbytes, 'context' => $context];
    }

    /**
     * Options for editing of certification image.
     *
     * @return array
     */
    public static function get_image_filemanager_options(): array {
        global $CFG;
        return ['maxbytes' => $CFG->maxbytes, 'maxfiles' => 1, 'subdirs' => 0 , 'accepted_types' => ['.jpg', '.jpeg', '.jpe', '.png']];
    }

    /**
     * Add new certification.
     *
     * NOTE: no access control done, includes hacks for form submission.
     *
     * @param stdClass $data
     * @return stdClass certification record
     */
    public static function add_certification(stdClass $data): stdClass {
        global $DB, $CFG;
        $data = clone($data);

        $trans = $DB->start_delegated_transaction();

        $context = \context::instance_by_id($data->contextid);
        if (!($context instanceof \context_system) && !($context instanceof \context_coursecat)) {
            throw new \coding_exception('certification contextid must be a system or course category');
        }

        if (trim($data->fullname ?? '') === '') {
            throw new \coding_exception('certification fullname is required');
        }

        if (trim($data->idnumber ?? '') === '') {
            throw new \coding_exception('certification idnumber is required');
        }

        $editorused = false;
        $rawdescription = null;
        if (isset($data->description_editor)) {
            $rawdescription = $data->description_editor['text'];
            $data->description = $rawdescription;
            $data->descriptionformat = $data->description_editor['format'];
            $editorused = true;
        } else if (!isset($data->description)) {
            $data->description = '';
        }
        if (!isset($data->descriptionformat)) {
            $data->descriptionformat = FORMAT_HTML;
        }

        $data->presentationjson = util::json_encode([]);
        unset($data->presentation);

        $data->public = isset($data->public) ? (int)(bool)$data->public : 0;
        $data->archived = isset($data->archived) ? (int)(bool)$data->archived : 0;

        $data->periodsjson = util::json_encode(self::get_periods_defaults());

        if (isset($data->programid1)) {
            if (!$DB->record_exists('enrol_programs_programs', ['id' => $data->programid1])) {
                throw new \invalid_parameter_exception('Invalid programid1');
            }
            if (isset($data->recertify)) {
                if (isset($data->programid2)) {
                    if (!$DB->record_exists('enrol_programs_programs', ['id' => $data->programid2])) {
                        throw new \invalid_parameter_exception('Invalid programid2');
                    }
                } else {
                    $data->programid2 = $data->programid1;
                }
            } else {
                $data->recertify = null;
                $data->programid2 = null;
            }
        } else {
            if (isset($data->programid2) && $DB->record_exists('enrol_programs_programs', ['id' => $data->programid2])) {
                throw new \invalid_parameter_exception('Unexpected programid2');
            }
            $data->programid1 = null;
            $data->programid2 = null;
        }

        $data->timecreated = time();
        $data->id = $DB->insert_record('tool_certify_certifications', $data);

        self::update_certification_image($data);

        if ($CFG->usetags && isset($data->tags)) {
            \core_tag_tag::set_item_tags('tool_certify', 'certification', $data->id, $context, $data->tags);
        }

        if ($editorused) {
            $editoroptions = self::get_description_editor_options($data->contextid);
            $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $editoroptions['context'],
                'tool_certify', 'description', $data->id);
            if ($rawdescription !== $data->description) {
                $DB->set_field('tool_certify_certifications', 'description', $data->description, ['id' => $data->id]);
            }
        }

        $certification = self::make_snapshot($data->id, 'add');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_created::create_from_certification($certification);
        $event->trigger();

        return $certification;
    }

    /**
     * Update general certification settings.
     *
     * @param stdClass $data
     * @return stdClass certification record
     */
    public static function update_certification_general(stdClass $data): stdClass {
        global $DB, $CFG;

        $data = clone($data);

        $trans = $DB->start_delegated_transaction();

        $oldcertification = $DB->get_record('tool_certify_certifications', ['id' => $data->id], '*', MUST_EXIST);

        $record = new stdClass();
        $record->id = $oldcertification->id;

        if (isset($data->contextid) && $data->contextid != $oldcertification->contextid) {
            // Cohort was moved to another context.
            $context = \context::instance_by_id($data->contextid);
            if (!($context instanceof \context_system) && !($context instanceof \context_coursecat)) {
                throw new \coding_exception('certification contextid must be a system or course category');
            }
            // The category pre-delete hook should be called before the category delete,
            // so the $oldcontext should be still here.
            $oldcontext = \context::instance_by_id($oldcertification->contextid, IGNORE_MISSING);
            if ($oldcontext) {
                get_file_storage()->move_area_files_to_new_context($oldcertification->contextid, $context->id,
                    'tool_certify', 'description', $data->id);
                // Delete tags even if they are not enabled before move,
                // tags API is not designed to deal with this,
                // we cannot create instance of deleted context.
                \core_tag_tag::set_item_tags('tool_certify', 'certification', $data->id, $oldcontext, null);
            }
            $record->contextid = $context->id;
        } else {
            $record->contextid = $oldcertification->contextid;
            $context = \context::instance_by_id($record->contextid);
        }

        if (isset($data->fullname)) {
            if (trim($data->fullname) === '') {
                throw new \coding_exception('certification fullname is required');
            }
            $record->fullname = $data->fullname;
        }
        if (isset($data->idnumber)) {
            if (trim($data->idnumber) === '') {
                throw new \coding_exception('certification idnumber is required');
            }
            $record->idnumber = $data->idnumber;
        }

        if (isset($data->description_editor)) {
            $data->description = $data->description_editor['text'];
            $data->descriptionformat = $data->description_editor['format'];
            $editoroptions = self::get_description_editor_options($data->contextid);
            $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $editoroptions['context'],
                'tool_certify', 'description', $data->id);
        }
        if (isset($data->description)) {
            $record->description = $data->description;
        }
        if (isset($data->descriptionformat)) {
            $record->descriptionformat = $data->descriptionformat;
        }
        if (isset($data->archived)) {
            $record->archived = (int)(bool)$data->archived;
        }

        $DB->update_record('tool_certify_certifications', $record);

        if ($CFG->usetags && isset($data->tags)) {
            \core_tag_tag::set_item_tags('tool_certify', 'certification', $data->id, $context, $data->tags);
        }

        $certification = self::update_certification_image($data);

        $certification = self::make_snapshot($certification->id, 'update_general');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_updated::create_from_certification($certification);
        $event->trigger();

        \tool_certify\local\assignment::fix_assignment_sources($certification->id, null);
        \enrol_programs\local\source\certify::sync_certifications($certification->id, null);

        return $certification;
    }

    /**
     * Update certification image changed via file manager.
     *
     * @param stdClass $data
     * @return stdClass certification record
     */
    private static function update_certification_image(stdClass $data): stdClass {
        global $DB;

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $data->id], '*', MUST_EXIST);
        $context = \context::instance_by_id($certification->contextid);

        if (isset($data->image)) {
            file_save_draft_area_files($data->image, $context->id, 'tool_certify', 'image', $data->id, array('subdirs' => 0, 'maxfiles' => 1));
            $files = get_file_storage()->get_area_files($context->id, 'tool_certify', 'image', $data->id, '', false);
            $presenation = (array)json_decode($certification->presentationjson);
            if ($files) {
                $file = reset($files);
                $presenation['image'] = $file->get_filename();
            } else {
                unset($presenation['image']);
            }
            $DB->set_field('tool_certify_certifications', 'presentationjson', util::json_encode($presenation), ['id' => $certification->id]);
            $certification = $DB->get_record('tool_certify_certifications', ['id' => $data->id], '*', MUST_EXIST);
        }

        return $certification;
    }

    /**
     * Update certification visibility.
     *
     * @param stdClass $data
     * @return stdClass certification record
     */
    public static function update_certification_visibility(stdClass $data): stdClass {
        global $DB;

        if ((isset($data->cohorts) && !is_array($data->cohorts))
            || empty($data->id) || !isset($data->public)) {

            throw new \coding_exception('Invalid data');
        }

        $trans = $DB->start_delegated_transaction();

        $oldcertification = $DB->get_record('tool_certify_certifications', ['id' => $data->id], '*', MUST_EXIST);

        if ($oldcertification->public != $data->public) {
            $DB->set_field('tool_certify_certifications', 'public', (int)(bool)$data->public, ['id' => $data->id]);
        }

        if (isset($data->cohorts)) {
            $oldcohorts = management::fetch_current_cohorts_menu($data->id);
            $oldcohorts = array_keys($oldcohorts);
            $oldcohorts = array_flip($oldcohorts);
            foreach ($data->cohorts as $cid) {
                if (isset($oldcohorts[$cid])) {
                    unset($oldcohorts[$cid]);
                    continue;
                }
                $record = (object)['certificationid' => $data->id, 'cohortid' => $cid];
                $DB->insert_record('tool_certify_cohorts', $record);
            }
            foreach ($oldcohorts as $cid => $unused) {
                $DB->delete_records('tool_certify_cohorts', ['certificationid' => $data->id, 'cohortid' => $cid]);
            }
        }

        $certification = self::make_snapshot($data->id, 'update_visibility');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_updated::create_from_certification($certification);
        $event->trigger();

        \tool_certify\local\assignment::fix_assignment_sources($certification->id, null);
        \enrol_programs\local\source\certify::sync_certifications($certification->id, null);

        return $certification;
    }

    /**
     * Update certification period settings.
     *
     * @param stdClass $data
     * @return stdClass certification record
     */
    public static function update_certification_settings(stdClass $data): stdClass {
        global $DB;

        if (empty($data->id)) {
            throw new \coding_exception('Invalid data');
        }

        $trans = $DB->start_delegated_transaction();

        $oldcertification = $DB->get_record('tool_certify_certifications', ['id' => $data->id], '*', MUST_EXIST);
        $periods = (object)json_decode($oldcertification->periodsjson, true);

        $record = new stdClass();
        $record->id = $oldcertification->id;

        if (property_exists($data, 'programid1') && $oldcertification->programid1 != $data->programid1) {
            if ($data->programid1) {
                $program = $DB->get_record('enrol_programs_programs', ['id' => $data->programid1], '*', MUST_EXIST);
                $record->programid1 = $program->id;
            } else {
                $record->programid1 = null;
            }
        } else {
            $record->programid1 = $oldcertification->programid1;
        }
        if (property_exists($data, 'resettype1')) {
            if (!array_key_exists($data->resettype1, self::get_resettype_options())) {
                throw new \invalid_parameter_exception('invalid resettype1');
            }
            $periods->resettype1 = $data->resettype1;
        }
        if (property_exists($data, 'due1')) {
            $periods->due1 = $data->due1;
            if ($periods->due1 <= 0) {
                $periods->due1 = null;
            }
        }
        if (property_exists($data, 'valid1')) {
            if (!array_key_exists($data->valid1, self::get_valid_options())) {
                throw new \invalid_parameter_exception('invalid valid1');
            }
            $periods->valid1 = $data->valid1;
        }
        if (isset($data->windowend1)) {
            if (!array_key_exists($data->windowend1['since'], self::get_windowend_options())) {
                throw new \invalid_parameter_exception('invalid windowend1');
            }
            $periods->windowend1['since'] = $data->windowend1['since'];
            if ($periods->windowend1['since'] === self::SINCE_NEVER) {
                $periods->windowend1['delay'] = null;
            } else if (array_key_exists('delay', $data->windowend1)) {
                $periods->windowend1['delay'] = util::normalise_delay($data->windowend1['delay']);
            } else {
                $periods->windowend1['delay'] = util::get_submitted_delay('windowend1', $data);
            }
        }
        if (isset($data->expiration1)) {
            if (!array_key_exists($data->expiration1['since'], self::get_expiration_options())) {
                throw new \invalid_parameter_exception('invalid expiration1');
            }
            $periods->expiration1['since'] = $data->expiration1['since'];
            if ($periods->expiration1['since'] === self::SINCE_NEVER) {
                $periods->expiration1['delay'] = null;
            } else if (array_key_exists('delay', $data->expiration1)) {
                $periods->expiration1['delay'] = util::normalise_delay($data->expiration1['delay']);
            } else {
                $periods->expiration1['delay'] = util::get_submitted_delay('expiration1', $data);
            }
        }
        if (property_exists($data, 'recertify')) {
            $record->recertify = $data->recertify;
            if (!$record->recertify) {
                $record->recertify = null;
            }
            if (!isset($oldcertification->recertify) && isset($record->recertify)) {
                if (isset($periods->expiration1)) {
                    $periods->expiration2 = $periods->expiration1;
                }
                $record->programid2 = $record->programid1;
            }
        } else {
            $record->recertify = $oldcertification->recertify;
        }

        if ($record->recertify === null) {
            // Remove program reference to prevent security issues,
            // because we do not verify access for previous program values.
            $record->programid2 = null;
            // Keep only the window2 end and expiration2 in sync, not valid2 and resettype.
            $periods->windowend2 = $periods->windowend1;
            $periods->expiration2 = $periods->expiration1;
        } else {
            if (property_exists($data, 'programid2') && $oldcertification->programid2 != $data->programid2) {
                if ($data->programid2) {
                    $program = $DB->get_record('enrol_programs_programs', ['id' => $data->programid2], '*', MUST_EXIST);
                    $record->programid2 = $program->id;
                } else {
                    $record->programid2 = null;
                }
            } else {
                $record->programid2 = $oldcertification->programid2;
            }
            if ($record->programid1 && !$record->programid2) {
                $record->programid2 = $record->programid1;
            }
            if (property_exists($data, 'grace2')) {
                if (!$data->grace2) {
                    $periods->grace2 = null;
                } else {
                    $periods->grace2 = (int)$data->grace2;
                }
            }
            if (property_exists($data, 'resettype2')) {
                if (!array_key_exists($data->resettype2, self::get_resettype_options())) {
                    throw new \invalid_parameter_exception('invalid resettype2');
                }
                $periods->resettype2 = $data->resettype2;
            }
            if (property_exists($data, 'valid2')) {
                if (!array_key_exists($data->valid2, self::get_valid_options())) {
                    throw new \invalid_parameter_exception('invalid valid2');
                }
                $periods->valid2 = $data->valid2;
            }
            if (isset($data->windowend2)) {
                if (!array_key_exists($data->windowend2['since'], self::get_windowend_options())) {
                    throw new \invalid_parameter_exception('invalid windowend2');
                }
                $periods->windowend2['since'] = $data->windowend2['since'];
                if ($periods->windowend2['since'] === self::SINCE_NEVER) {
                    $periods->windowend2['delay'] = null;
                } else if (array_key_exists('delay', $data->windowend2)) {
                    $periods->windowend2['delay'] = util::normalise_delay($data->windowend2['delay']);
                } else {
                    $periods->windowend2['delay'] = util::get_submitted_delay('windowend2', $data);
                }
            }
            if (isset($data->expiration2)) {
                if (!array_key_exists($data->expiration2['since'], self::get_expiration_options())) {
                    throw new \invalid_parameter_exception('invalid expiration2');
                }
                $periods->expiration2['since'] = $data->expiration2['since'];
                if ($periods->expiration2['since'] === self::SINCE_NEVER) {
                    $periods->expiration2['delay'] = null;
                } else if (array_key_exists('delay', $data->expiration2)) {
                    $periods->expiration2['delay'] = util::normalise_delay($data->expiration2['delay']);
                } else {
                    $periods->expiration2['delay'] = util::get_submitted_delay('expiration2', $data);
                }
            }
        }

        $record->periodsjson = util::json_encode($periods);

        $DB->update_record('tool_certify_certifications', $record);

        $certification = self::make_snapshot($record->id, 'update_settings');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_updated::create_from_certification($certification);
        $event->trigger();

        \tool_certify\local\assignment::fix_assignment_sources($certification->id, null);
        \enrol_programs\local\source\certify::sync_certifications($certification->id, null);

        return $certification;
    }

    /**
     * Update certificate template.
     *
     * @param int $certificationid
     * @param int|null $templateid
     * @return stdClass
     */
    public static function update_certificate(int $certificationid, ?int $templateid): stdClass {
        global $DB;

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $certificationid], '*', MUST_EXIST);
        if ($templateid) {
            $template = $DB->get_record('tool_certificate_templates', ['id' => $templateid], '*', MUST_EXIST);
            $templateid = $template->id;
        } else {
            $templateid = null;
        }

        if ($templateid == $certification->templateid) {
            return $certification;
        }

        $trans = $DB->start_delegated_transaction();

        $DB->set_field('tool_certify_certifications', 'templateid', $templateid, ['id' => $certification->id]);

        $certification = self::make_snapshot($certification->id, 'update_certificate');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_updated::create_from_certification($certification);
        $event->trigger();

        return $certification;
    }

    /**
     * Delete certification.
     *
     * @param int $id
     * @return void
     */
    public static function delete_certification(int $id): void {
        global $DB;

        $trans = $DB->start_delegated_transaction();

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $id], '*', MUST_EXIST);
        $context = \context::instance_by_id($certification->contextid);

        // Delete notifications configuration and data.
        notification_manager::delete_certification_notifications($certification);

        $DB->delete_records('tool_certify_assignments', ['certificationid' => $certification->id]);
        $sources = $DB->get_records('tool_certify_sources', ['certificationid' => $certification->id]);
        foreach ($sources as $source) {
            $DB->delete_records('tool_certify_requests', ['sourceid' => $source->id]);
            $DB->delete_records('tool_certify_src_cohorts', ['sourceid' => $source->id]);
        }
        unset($sources);
        $DB->delete_records('tool_certify_sources', ['certificationid' => $certification->id]);
        $DB->delete_records('tool_certify_cohorts', ['certificationid' => $certification->id]);
        $DB->delete_records('tool_certify_periods', ['certificationid' => $certification->id]);

        // Certification details last.
        \core_tag_tag::set_item_tags('tool_certify', 'certification', $certification->id, $context, null);
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'tool_certify', 'description', $certification->id);
        $fs->delete_area_files($context->id, 'tool_certify', 'image', $certification->id);

        $DB->delete_records('tool_certify_certifications', ['id' => $certification->id]);

        self::make_snapshot($certification->id, 'delete');

        $trans->allow_commit();

        $event = \tool_certify\event\certification_deleted::create_from_certification($certification);
        $event->trigger();

        // Deal with leftover program allocations.
        \enrol_programs\local\source\certify::sync_certifications($certification->id, null);
    }

    /**
     * Make a full certification snapshot.
     *
     * @param int $certificationid
     * @param string $reason
     * @param string|null $explanation
     * @return \stdClass|null null of certification does not exist any more, program record otherwise
     */
    public static function make_snapshot(int $certificationid, string $reason, ?string $explanation = null): ?\stdClass {
        global $DB, $USER;

        $data = new \stdClass();
        $data->certificationid = $certificationid;
        $data->reason = $reason;
        $data->timesnapshot = time();
        if ($USER->id > 0) {
            $data->snapshotby = $USER->id;
        }
        $data->explanation = $explanation;

        if ($reason === 'delete') {
            if ($DB->record_exists('tool_certify_certifications', ['id' => $certificationid])) {
                throw new \coding_exception('deleted certification must not exist');
            }
            $DB->insert_record('tool_certify_crt_snapshots', $data);
            return null;
        }

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $certificationid], '*', MUST_EXIST);
        $data->certificationjson = util::json_encode($certification);
        $data->cohortsjson = util::json_encode($DB->get_records('tool_certify_cohorts', ['certificationid' => $certification->id], 'id ASC'));
        $data->sourcesjson = util::json_encode($DB->get_records('tool_certify_sources', ['certificationid' => $certification->id], 'id ASC'));

        $DB->insert_record('tool_certify_crt_snapshots', $data);

        return $certification;
    }

    /**
     * Called before course category is deleted.
     *
     * @param stdClass $category
     * @return void
     */
    public static function pre_course_category_delete(stdClass $category): void {
        global $DB;

        $catcontext = \context_coursecat::instance($category->id, MUST_EXIST);
        $parentcontext = $catcontext->get_parent_context();

        $certifications = $DB->get_records('tool_certify_certifications', ['contextid' => $catcontext->id]);
        foreach ($certifications as $certification) {
            $data = (object)[
                'id' => $certification->id,
                'contextid' => $parentcontext->id,
            ];
            self::update_certification_general($data);
        }
    }

    /**
     * Returns defaults for period settings in new certifications.
     *
     * @return stdClass
     */
    public static function get_periods_defaults(): stdClass {
        // NOTE: we should probably add admin settings for defaults later...
        return (object)[
            'resettype1' => course_reset::RESETTYPE_STANDARD,
            'due1' => null,
            'valid1' => self::SINCE_CERTIFIED,
            'windowend1' => ['since' => self::SINCE_NEVER, 'delay' => null],
            'expiration1' => ['since' => self::SINCE_NEVER, 'delay' => null],
            'grace2' => null,
            'resettype2' => course_reset::RESETTYPE_STANDARD,
            'valid2' => self::SINCE_WINDOWDUE,
            'windowend2' => ['since' => self::SINCE_NEVER, 'delay' => null],
            'expiration2' => ['since' => self::SINCE_NEVER, 'delay' => null],
        ];
    }

    /**
     * Returns settings for periods.
     *
     * @param stdClass $certification
     * @return stdClass
     */
    public static function get_periods_settings(stdClass $certification): stdClass {
        $resettypes = self::get_resettype_options();
        $validoptions = certification::get_valid_options();
        $windowendoptions = certification::get_windowend_options();
        $expirationoptions = certification::get_expiration_options();
        $defaults = self::get_periods_defaults();

        $settings = (object)json_decode($certification->periodsjson, true);
        $settings->programid1 = $certification->programid1;
        $settings->programid2 = ($certification->programid2 ?? $certification->programid1);
        $settings->recertify = $certification->recertify;

        if (!isset($settings->resettype1) || !isset($resettypes[$settings->resettype1])) {
            debugging("invalid resettype1 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->resettype1 = $defaults->resettype1;
        }

        if (!property_exists($settings, 'due1')) {
            debugging("invalid due1 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->due1 = $defaults->due1;
        }

        if (!isset($settings->valid1) || !isset($validoptions[$settings->valid1])) {
            debugging("invalid valid1 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->valid1 = $defaults->valid1;
        }

        if (!property_exists($settings, 'windowend1') || !isset($windowendoptions[$settings->windowend1['since']])) {
            debugging("invalid windowend1 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->windowend1 = $defaults->windowend1;
        }

        if (!property_exists($settings, 'expiration1') || !isset($expirationoptions[$settings->expiration1['since']])) {
            debugging("invalid expiration1 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->expiration1 = $defaults->expiration1;
        }

        if (!property_exists($settings, 'grace2')) {
            debugging("invalid grace2 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->grace2 = $defaults->grace2;
        }

        if (!isset($settings->resettype2) || !isset($resettypes[$settings->resettype2])) {
            debugging("invalid resettype2 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->resettype2 = $defaults->resettype2;
        }

        if (!isset($settings->valid2) || !isset($validoptions[$settings->valid2])) {
            debugging("invalid valid2 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->valid2 = $settings->valid1;
        }

        if (!property_exists($settings, 'windowend2')  || !isset($windowendoptions[$settings->windowend2['since']])) {
            debugging("invalid windowend2 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->windowend2 = $settings->windowend1;
        }

        if (!property_exists($settings, 'expiration2') || !isset($expirationoptions[$settings->expiration2['since']])) {
            debugging("invalid expiration2 detected in $certification->id certification", DEBUG_DEVELOPER);
            $settings->expiration2 = $settings->expiration1;
        }

        return $settings;
    }

    /**
     * Recertification reset types.
     *
     * NOTE: higher number means more data is purged.
     *
     * @return array
     */
    public static function get_resettype_options(): array {
        $result = [
            course_reset::RESETTYPE_NONE => new \lang_string('resettype_none', 'enrol_programs'),
            course_reset::RESETTYPE_DEALLOCATE => new \lang_string('resettype_deallocate', 'enrol_programs'),
            course_reset::RESETTYPE_STANDARD => new \lang_string('resettype_standard', 'enrol_programs'),
            course_reset::RESETTYPE_FULL => new \lang_string('resettype_full', 'enrol_programs'),
        ];
        return $result;
    }

    /**
     * Valid until relative date types.
     *
     * @return \lang_string[]
     */
    public static function get_valid_options(): array {
        return [
            self::SINCE_CERTIFIED => new \lang_string('certifieddate', 'tool_certify'),
            self::SINCE_WINDOWSTART => new \lang_string('windowstartdate', 'tool_certify'),
            self::SINCE_WINDOWDUE => new \lang_string('windowduedate', 'tool_certify'),
            self::SINCE_WINDOWEND => new \lang_string('windowenddate', 'tool_certify'),
        ];
    }

    /**
     * Window end relative date types.
     *
     * @return \lang_string[]
     */
    public static function get_windowend_options(): array {
        return [
            self::SINCE_NEVER => new \lang_string('never', 'tool_certify'),
            self::SINCE_WINDOWSTART => new \lang_string('windowstartdate', 'tool_certify'),
            self::SINCE_WINDOWDUE => new \lang_string('windowduedate', 'tool_certify'),
        ];
    }

    /**
     * Certification expiration relative date types.
     *
     * @return \lang_string[]
     */
    public static function get_expiration_options(): array {
        return [
            self::SINCE_NEVER => new \lang_string('never', 'tool_certify'),
            self::SINCE_CERTIFIED => new \lang_string('certifieddate', 'tool_certify'),
            self::SINCE_WINDOWSTART => new \lang_string('windowstartdate', 'tool_certify'),
            self::SINCE_WINDOWDUE => new \lang_string('windowduedate', 'tool_certify'),
            self::SINCE_WINDOWEND => new \lang_string('windowenddate', 'tool_certify'),
        ];
    }
}
