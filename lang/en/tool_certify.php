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
 * Certifications plugin language file.
 *
 * @package    tool_certify
 * @copyright  2023 Open LMS (https://www.openlms.net/)
 * @author     Petrs Skoda
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['addcertification'] = 'Add certification';
$string['addperiod'] = 'Add period';
$string['allcertifications'] = 'All certifications';
$string['archived'] = 'Archived';
$string['assignments'] = 'Assignments';
$string['benefitname'] = '{$a}: Certification assignment';
$string['assignmentsources'] = 'Assignment sources';
$string['catalogue'] = 'Certification catalogue';
$string['catalogue_dofilter'] = 'Search';
$string['catalogue_resetfilter'] = 'Clear';
$string['catalogue_searchtext'] = 'Search text';
$string['catalogue_tag'] = 'Filter by tag';
$string['certificates'] = 'Certificates';
$string['certification'] = 'Certification';
$string['certificationidnumber'] = 'Certification idnumber';
$string['certificationimage'] = 'Certification image';
$string['certificationname'] = 'Certification name';
$string['certifications'] = 'Certifications';
$string['certificationsactive'] = 'Active';
$string['certificationsarchived'] = 'Archived';
$string['certificationstatus'] = 'Certification status';
$string['certificationstatus_any'] = 'Any';
$string['certificationstatus_archived'] = 'Archived';
$string['certificationstatus_expired'] = 'Expired';
$string['certificationstatus_notcertified'] = 'Not certified';
$string['certificationstatus_temporary'] = 'Temporary valid';
$string['certificationstatus_valid'] = 'Valid';
$string['certificationurl'] = 'Certification URL';
$string['certifieddate'] = 'Certification completion date';
$string['certifieduntiltemporary'] = 'Temporary certification until';
$string['certify:admin'] = 'Advanced certification administration';
$string['certify:assign'] = 'Assign certifications';
$string['certify:delete'] = 'Delete certifications';
$string['certify:edit'] = 'Update certifications';
$string['certify:view'] = 'View certifications';
$string['certify:viewcatalogue'] = 'Access certifications catalogue';
$string['cohorts'] = 'Visible to cohorts';
$string['cohorts_help'] = 'Non-public certifications can be made visible to specified cohort members.

Visibility status does not affect already assigned certifications.';
$string['delayafter'] = '{$a->delay} after {$a->after}';
$string['delaybefore'] = '{$a->delay} before {$a->before}';
$string['deleteassignment'] = 'Delete assignment';
$string['deletecertification'] = 'Delete certification';
$string['deleteperiod'] = 'Delete period';
$string['errornoassignment'] = 'Certification is not assigned';
$string['errornoassignments'] = 'No certification assignments found.';
$string['errornocertifications'] = 'No certifications found.';
$string['errornomycertifications'] = 'No assigned certifications found.';
$string['errornorequests'] = 'No program requests found';
$string['event_certification_created'] = 'Certification created';
$string['event_certification_deleted'] = 'Certification deleted';
$string['event_certification_updated'] = 'Certification updated';
$string['event_user_assigned'] = 'User assigned to certification';
$string['event_user_certified'] = 'User was certified';
$string['event_user_unassigned'] = 'User was un-assigned from certification';
$string['expirationafter'] = 'Expires after';
$string['fromdate'] = 'Valid from';
$string['graceperiod'] = 'Grace period';
$string['management'] = 'Certification management';
$string['messageprovider:approval_request_notification'] = 'Certification approval request notification';
$string['messageprovider:approval_reject_notification'] = 'Certification request rejection notification';
$string['messageprovider:assignment_notification'] = 'Certification assignment notification';
$string['messageprovider:unassignment_notification'] = 'Certification un-assignment notification';
$string['messageprovider:valid_notification'] = 'Certification validity notification';
$string['mycertifications'] = 'My certifications';
$string['never'] = 'Never';
$string['notallocated'] = 'Not allocated';
$string['notifications'] = 'Certification notifications';
$string['notification_assignment'] = 'User assigned';
$string['notification_assignment_body'] = 'Hello {$a->user_fullname},

you have been assigned to certification "{$a->certification_fullname}".';
$string['notification_assignment_description'] = 'Notification sent to users when they are assigned to certification.';
$string['notification_assignment_subject'] = 'Certification assignment notification';
$string['notification_valid'] = 'Valid certification';
$string['notification_valid_body'] = 'Hello {$a->user_fullname},

your certification "{$a->certification_fullname}" is now valid:

* valid from: {$a->period_fromdate}
* expires on: {$a->period_untildate}
* recertification opens on: {$a->period_recertificationdate}
';
$string['notification_valid_description'] = 'Notification sent to users when their certification becomes valid.';
$string['notification_valid_subject'] = 'Valid certification notification';
$string['notification_unassignment'] = 'User unassigned';
$string['notification_unassignment_body'] = 'Hello {$a->user_fullname},

you have been un-assigned from certification "{$a->certification_fullname}".';
$string['notification_unassignment_description'] = 'Notification sent to users when they are un-assigned from certification.';
$string['notification_unassignment_subject'] = 'Certification un-assignment notification';
$string['notificationdates'] = 'Notifications';
$string['notset'] = 'Not set';
$string['period'] = 'Certification period';
$string['periods'] = 'Certification periods';
$string['periodstatus'] = 'Status';
$string['periodstatus_archived'] = 'Archived';
$string['periodstatus_certified'] = 'Certified';
$string['periodstatus_expired'] = 'Expired';
$string['periodstatus_failed'] = 'Failed';
$string['periodstatus_future'] = 'Future';
$string['periodstatus_overdue'] = 'Overdue';
$string['periodstatus_pending'] = 'Pending';
$string['periodstatus_revoked'] = 'Revoked';
$string['pluginname'] = 'Certifications';
$string['pluginname_desc'] = 'Open LMS certification and re-certification tool';
$string['privacy:metadata:field:archived'] = 'Archived flag';
$string['privacy:metadata:field:assignmentid'] = 'Assignment id';
$string['privacy:metadata:field:certificationid'] = 'Certification id';
$string['privacy:metadata:field:datajson'] = 'Data JSON';
$string['privacy:metadata:field:explanation'] = 'Snapshot explanation';
$string['privacy:metadata:field:programid'] = 'Program id';
$string['privacy:metadata:field:quantity'] = 'Quantity';
$string['privacy:metadata:field:reason'] = 'Snapshot reason';
$string['privacy:metadata:field:rejectedby'] = 'Rejected by';
$string['privacy:metadata:field:snapshotby'] = 'Snapshot by';
$string['privacy:metadata:field:sourceid'] = 'Source id';
$string['privacy:metadata:field:timecertified'] = 'Certification date';
$string['privacy:metadata:field:timecertifieduntil'] = 'Temporary certified until date';
$string['privacy:metadata:field:timefrom'] = 'Certified from date';
$string['privacy:metadata:field:timerejected'] = 'Rejection date';
$string['privacy:metadata:field:timerequested'] = 'Request date';
$string['privacy:metadata:field:timerevoked'] = 'Certification revocation date';
$string['privacy:metadata:field:timesnapshot'] = 'Snapshot date';
$string['privacy:metadata:field:timeuntil'] = 'Certified until date';
$string['privacy:metadata:field:timewindowdue'] = 'Window due date';
$string['privacy:metadata:field:timewindowend'] = 'Window end date';
$string['privacy:metadata:field:timewindowstart'] = 'Window start date';
$string['privacy:metadata:field:userid'] = 'User id';
$string['privacy:metadata:table:tool_certify_assignments'] = 'User assignments table';
$string['privacy:metadata:table:tool_certify_periods'] = 'Certification periods table';
$string['privacy:metadata:table:tool_certify_requests'] = 'Certification requests table';
$string['privacy:metadata:table:tool_certify_src_commholds'] = 'Commerce allocation reservations';
$string['privacy:metadata:table:tool_certify_usr_snapshots'] = 'User certification snapshots table';
$string['program1'] = 'Certification program';
$string['program2'] = 'Re-certification program';
$string['public'] = 'Public';
$string['public_help'] = 'Public certifications are visible to all users.

Visibility status does not affect already assigned certifications.';
$string['purchaseaccess'] = 'Purchase access';
$string['recertification'] = 'Re-certification';
$string['recertifications'] = 'Re-certifications';
$string['recertify'] = 'Re-certify automatically';
$string['recertifybefore'] = 'Re-certify before expiry';
$string['recertifyifexpired'] = 'If expired';
$string['resettype1'] = 'Certification program reset';
$string['resettype2'] = 'Re-certification program reset';
$string['revokeddate'] = 'Revocation date';
$string['selectcategory'] = 'Select category';
$string['source'] = 'Source';
$string['source_approval'] = 'Requests with approval';
$string['source_approval_allownew'] = 'Allow approvals';
$string['source_approval_allownew_desc'] = 'Allow adding new _requests with approval_ sources to certifications';
$string['source_approval_allowrequest'] = 'Allow new requests';
$string['source_approval_confirm'] = 'Please confirm that you want to request assignment to the certification.';
$string['source_approval_daterequested'] = 'Date requested';
$string['source_approval_daterejected'] = 'Date rejected';
$string['source_approval_makerequest'] = 'Request access';
$string['source_approval_notification_approval_request_subject'] = 'Certification request notification';
$string['source_approval_notification_approval_request_body'] = '
User {$a->user_fullname} requested access to certification "{$a->certification_fullname}".
';
$string['source_approval_notification_approval_reject_subject'] = 'Certification request rejection notification';
$string['source_approval_notification_approval_reject_body'] = 'Hello {$a->user_fullname},

your request to access "{$a->certification_fullname}" certification was rejected.

{$a->reason}
';
$string['source_approval_requestallowed'] = 'Requests are allowed';
$string['source_approval_requestnotallowed'] = 'Requests are not allowed';
$string['source_approval_requests'] = 'Requests';
$string['source_approval_requestpending'] = 'Access request pending';
$string['source_approval_requestrejected'] = 'Access request was rejected';
$string['source_approval_requestapprove'] = 'Approve request';
$string['source_approval_requestreject'] = 'Reject request';
$string['source_approval_requestdelete'] = 'Delete request';
$string['source_approval_rejectionreason'] = 'Rejection reason';
$string['source_cohort'] = 'Automatic cohort assignment';
$string['source_cohort_cohortstoassign'] = 'Assign to cohorts';
$string['source_ecommerce'] = 'E-Commerce assignment';
$string['source_ecommerce_allownew'] = 'Allow e-commerce assignment';
$string['source_ecommerce_allownew_desc'] = 'Allow adding new e-commerce auto allocation sources to certifications';;
$string['source_ecommerce_allowsignup'] = 'Allow new assignments';
$string['source_ecommerce_cohortmembershiprequirement'] = 'Users must be a member of one of the following cohorts: {$a}';
$string['source_ecommerce_maxusers'] = 'Max users';
$string['source_ecommerce_nocapacity'] = 'There is no remaining capacity on this certification';
$string['source_manual'] = 'Manual assignment';
$string['source_manual_assignusers'] = 'Assign users';
$string['source_selfassignment'] = 'Self assignment';
$string['source_selfassignment_assign'] = 'Sign up';
$string['source_selfassignment_allownew'] = 'Allow self assignment';
$string['source_selfassignment_allownew_desc'] = 'Allow adding new _self assignment_ sources to certifications';
$string['source_selfassignment_allowsignup'] = 'Allow new sign ups';
$string['source_selfassignment_confirm'] = 'Please confirm that you want to be assigned to the certification.';
$string['source_selfassignment_enable'] = 'Enable self assignment';
$string['source_selfassignment_key'] = 'Sign up key';
$string['source_selfassignment_keyrequired'] = 'Sign up key is required';
$string['source_selfassignment_maxusers'] = 'Max users';
$string['source_selfassignment_maxusersreached'] = 'Maximum number of users self-assigned already';
$string['source_selfassignment_maxusers_status'] = 'Users {$a->count}/{$a->max}';
$string['source_selfassignment_signupallowed'] = 'Sign ups are allowed';
$string['source_selfassignment_signupnotallowed'] = 'Sign ups are not allowed';
$string['stoprecertify'] = 'Re-certification stopped';
$string['tabassignment'] = 'Assignment settings';
$string['tabgeneral'] = 'General';
$string['tabsettings'] = 'Period settings';
$string['tabusers'] = 'Users';
$string['tabvisibility'] = 'Visibility settings';
$string['tagarea_certification'] = 'Certifications';
$string['taskcron'] = 'Certification cron task';
$string['tasktriggercertificate'] = 'Trigger certificate issuing cron asap';
$string['untildate'] = 'Expiration';
$string['updateassignment'] = 'Update assignment';
$string['updateassignments'] = 'Update assignment settings';
$string['updatecertificatetemplate'] = 'Update certificate template';
$string['updatecertification'] = 'Update certification';
$string['updateperiod'] = 'Override period dates';
$string['updaterecertification'] = 'Update re-certification';
$string['updatesource'] = 'Update {$a}';
$string['validfrom'] = 'Valid from';
$string['windowdueafter'] = 'Due after';
$string['windowduedate'] = 'Certification due';
$string['windowendafter'] = 'Window closing after';
$string['windowenddate'] = 'Window closing';
$string['windowstartdate'] = 'Window opening';
