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

namespace tool_certify\local\commerce;

use tool_certify\local\management;
use tool_certify\local\source\ecommerce;
use local_commerce\local\benefit;
use local_commerce\local\benefithandlerbase;

/**
 * Cetification benefit handler.
 *
 * @package tool_certify
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2023, Andrew Hancox
 */
class benefithandler extends benefithandlerbase {
    public static function benefitlookup($type, $instance): benefit {
        global $DB;
        $certification = $DB->get_field('tool_certify_certifications', 'id', ['idnumber' => $instance]);
        return benefit::get_record(['pluginname' => 'tool_certify', 'instance' => $certification]);
    }

    public function getbenefitname(): string {
        global $DB;

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $this->instance]);
        return get_string('benefitname', 'tool_certify', format_string($certification->fullname));
    }

    public function grantbenefit(int $userid, int $start, int $end, int $purchaseruserid, int $holdkey, array $params = [], $voucherredemptionid = null): bool {
        ecommerce::grantbenefit($this->instance, $userid, ['timewindowstart' => $start, 'timewindowend' => $end]);

        $this->releaseheldresources($holdkey, 1);

        return true;
    }

    public function benefitcurrentlypossessed(int $userid, int $holdkeyid = null, array $params = []): bool {
        global $DB;

        return $DB->record_exists('tool_certify_assignments', ['certificationid' => $this->instance, 'userid' => $userid]);
    }

    public function getredirecturl(int $holdkeyid, $voucherredemptionid = null): array {
        return [10, new \moodle_url('/admin/tool/certify/my/certification.php', ['id' => $this->instance])];
    }

    public function benefitcurrentlyavailable(int $quantity = 1, array $params = []): bool {
        global $DB;

        $certification = $DB->get_record('tool_certify_certifications', ['id' => $this->instance]);

        if ($certification->archived) {
            return false;
        }

        $source = $DB->get_record('tool_certify_sources', ['type' => ecommerce::get_type(), 'certificationid' => $certification->id], '*', MUST_EXIST);

        $data = (object)json_decode($source->datajson);
        if (isset($data->allowsignup) && !$data->allowsignup) {
            return false;
        }
        if (isset($data->maxusers)) {
            $heldspaces = $DB->get_field_sql('SELECT SUM(quantity) FROM {tool_certify_src_commholds} WHERE certificationid = :certificationid',
                ['certificationid' => $certification->id]);
            if (!isset($heldspaces)) {
                $heldspaces = 0;
            }

            // Any type of allocations.
            $count = $DB->count_records('tool_certify_assignments', ['certificationid' => $certification->id]);

            if ($heldspaces + $count + $quantity > $data->maxusers) {
                return false;
            }
        }

        return true;
    }

    public function releaseheldresources($holdkeyid, $quantity = null): bool {
        global $DB;

        if (!isset($quantity)) {
            $DB->delete_records('tool_certify_src_commholds', ['holdkey' => $holdkeyid, 'certificationid' => $this->instance]);
        } else {
            $record = $DB->get_record('tool_certify_src_commholds', ['holdkey' => $holdkeyid, 'certificationid' => $this->instance]);

            if (!empty($record)) { // This should almost never be empty - only scenario is when using the commerce choice benefit.
                $record->quantity = $record->quantity - $quantity;
                $DB->update_record('tool_certify_src_commholds', $record);
            }
        }

        return true;
    }

    public function releaseheldresourcesbyuserid($userid): bool {
        global $DB;

        $DB->delete_records('tool_certify_src_commholds', ['userid' => $userid, 'certificationid' => $this->instance]);

        return true;
    }

    public function holdresources($userid, $params, $holdkey): bool {
        global $DB;

        $DB->insert_record('tool_certify_src_commholds',
            (object)['userid' => $userid, 'quantity' => $params['quantity'] ?? 1, 'holdkey' => $holdkey, 'certificationid' => $this->instance]);

        return true;
    }

    public function getfailedtoholdmessage() {
        return get_string('source_ecommerce_nocapacity', 'tool_certify');
    }

    private function cohortrequirementmet($userid) {
        $cohorts = array_keys(management::fetch_current_cohorts_menu($this->instance));

        if (empty($cohorts)) {
            return true;
        }

        $usercohorts = array_keys(cohort_get_user_cohorts($userid));

        return !empty(array_intersect($cohorts, $usercohorts));
    }

    public function prerequisitesmet(int $userid, array $courseenrolsinproduct): bool {
        return parent::prerequisitesmet($userid, $courseenrolsinproduct) && $this->cohortrequirementmet($userid);
    }

    public function get_prerequisitewarnings(array $courseenrolsinproduct = []): array {
        $warnings = parent::get_prerequisitewarnings($courseenrolsinproduct);

        $cohorts = management::fetch_current_cohorts_menu($this->instance);

        if (!empty($cohorts)) {
            $cohorts = implode(', ', $cohorts);
            $warnings[] = get_string('source_ecommerce_cohortmembershiprequirement', 'tool_certify', $cohorts);
        }


        return $warnings;
    }
}
