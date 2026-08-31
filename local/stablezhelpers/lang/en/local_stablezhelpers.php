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

/**
 * Language file.
 *
 * @package    local_stablezhelpers
 * @copyright  2026 https://santoshmagar.com.np/
 * @author     santoshmagar.com.np
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin identification and configuration.
$string['pluginname'] = 'StableZ Helpers';
$string['pluginname_desc'] = 'Local plugin to manage course and user metadata';
$string['enable'] = 'Enable stablez helper';
$string['manage'] = 'Manage StableZ Helpers';

// General settings.
$string['general'] = 'General';
$string['generalsettings'] = 'General Settings';
$string['plugininfo'] = 'Plugin Information';
$string['tip'] = 'Tip';
// Common labels.
$string['sn'] = 'S.N';
$string['name'] = 'Name';
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['image'] = 'Image';
$string['images'] = 'Images';
$string['content'] = 'Content';
$string['status'] = 'Status';
$string['type'] = 'Type';
$string['author'] = 'Author';
$string['modified'] = 'Modified';
$string['actions'] = 'Actions';
$string['back'] = 'Back';
$string['viewallcontent'] = 'View all content';
$string['none'] = 'None';
$string['unknown'] = 'Unknown';
$string['editcustomcleanurl'] = 'Edit Custom Clean URL';

// Status values.
$string['draft'] = 'Draft';
$string['status_read'] = 'Read';
$string['status_unread'] = 'Unread';
$string['status_replied'] = 'Replied';
$string['publish'] = 'Publish';
$string['published'] = 'Published';

// Actions.
$string['savechanges'] = 'Save changes';
$string['filter'] = 'Filter';
$string['search'] = 'Search';
$string['all'] = 'All';
$string['readmore'] = 'Read More';

// Content-related.
$string['shortname'] = 'Short Name';
$string['shortname_help'] = 'A unique short name for this content. Used for identification.';
$string['parent'] = 'Parent Content';
$string['title_help'] = 'Enter the title of this content. This will be displayed as the main heading.';
$string['content_help'] = 'The main content of this page. You can use the editor to format text, add images, and embed multimedia.';
$string['image_filemanager_help'] = 'Upload an image to be used as the feature image for this content. Supported formats: JPG, PNG, GIF.';
$string['status_help'] = 'Check this box to publish this content and make it visible. Unchecked content will remain in draft status.';

// Section titles.
$string['contentmanagement'] = 'Content Management';
$string['customhelpercontentmanagement'] = 'Custom Helper Content Management';
$string['managecontent'] = 'Manage Content';

// Content actions.
$string['addnewcontent'] = 'Add New Content';
$string['addcontent'] = 'Add Content';
$string['editcontent'] = 'Edit Content';

// Content types.
$string['contenttype'] = 'Content Type';
$string['contenttype_page'] = 'Page';
$string['contenttype_faq'] = 'FAQ';
$string['contenttype_testimonial'] = 'Testimonial';
$string['contenttype_article'] = 'Article';
$string['contenttype_video'] = 'Video';
$string['contenttype_announcement'] = 'Announcement';

// Content messages.
$string['contentnotfound'] = 'Content not found';
$string['contentcreated'] = 'Content created successfully';
$string['contentdeleted'] = 'Content deleted successfully';
$string['changessaved'] = 'Changes saved successfully';
$string['errordeletecontent'] = 'Error deleting content';
$string['deletecontentconfirm'] = 'Are you sure you want to delete "{$a->title}"? This action cannot be undone.';
$string['invalidactionparam'] = 'Invalid action param. Check action param';
$string['failtoconfirmsesskey'] = 'Fail to confirm sesskey.';

// Capabilities.
$string['stablezhelpers:managecontent'] = 'Manage stablez content';
$string['stablezhelpers:viewcontent'] = 'View stablezcontent';
$string['stablezhelpers:viewdraft'] = 'View stablez draft content';

// Form fields.
$string['faq_category'] = 'FAQ category';
$string['faq_content'] = 'FAQ content';
$string['search_faq_category'] = 'Search FAQ categories';
$string['image_filemanager'] = 'Image File Manager';
$string['designation'] = 'Designation';
$string['backtosubmissions'] = 'Back to submissions';
$string['cleanup_task'] = 'Clean up expired data';

// User API messages.
$string['invalidlogin'] = 'Invalid login credentials.';
$string['usernotconfirmed'] = 'User account has not been confirmed.';
$string['usernotfound'] = 'User not found.';
$string['useridisrequired'] = 'User ID is required.';
$string['invalidtoken'] = 'Invalid session token.';
$string['logoutsuccess'] = 'Logout successful.';
$string['invaliduserid'] = 'Invalid user ID.';
$string['useralreadyenrolled'] = 'User is already enrolled.';
$string['userenrolledsuccessfully'] = 'User enrolled successfully.';

// Course API messages.
$string['invalidcourseid'] = 'Invalid course ID.';
$string['invalidroleid'] = 'Invalid role ID.';
$string['manualenrolmentnotfound'] = 'Manual enrolment method not found.';
$string['coursenotcompleted'] = 'Course not completed (requires 100% progress).';
$string['certificatenotconfigured'] = 'Certificate not configured for this course.';
$string['certificatedata'] = 'Certificate data retrieved successfully.';
$string['failtoissuecertificate'] = 'Failed to issue certificate.';
$string['invalidcertificatedate'] = 'Invalid certificate date. Must be between 2001-01-01 and 2100-12-31.';
$string['usernotstudent'] = 'User is not enrolled as a student in this course.';

// Role API messages.
$string['invalidrole'] = 'Invalid role.';
$string['rolenotassignable'] = 'Role is not assignable in this context.';
$string['rolealreadyassigned'] = 'Role is already assigned to this user.';
$string['roleassigned'] = 'Role assigned successfully.';
$string['roleunassigned'] = 'Role unassigned successfully.';
$string['roleisrequired'] = 'Role shortname is required.';
$string['onlysystemroleallowed'] = 'Only system-level role assignment is allowed.';
$string['useralreadyadmin'] = 'User is already an administrator.';
$string['usernotadmin'] = 'User is not an administrator.';
$string['adminassigned'] = 'Administrator role assigned successfully.';
$string['adminunassigned'] = 'Administrator role unassigned successfully.';
$string['adminroleonlysystem'] = 'Admin role can only be assigned at system level.';
$string['rolesynced'] = 'User roles synced successfully.';

// =============================================================================
// TESTIMONIALS AND FAQ STRINGS (ADDITIONAL)
// =============================================================================

$string['testimonial'] = 'Testimonial';
$string['faq'] = 'FAQ';
$string['page'] = 'Page';

// =============================================================================
// CONTENT API STRINGS
// =============================================================================

$string['titleisrequired'] = 'Title is required.';
$string['contentisrequired'] = 'Content is required.';
$string['errorcreatecontent'] = 'Error creating content.';

// =============================================================================
// CERTIFICATE STRINGS
// =============================================================================

$string['certificateissued'] = 'Certificate issued successfully.';
$string['certificatealreadyissued'] = 'Certificate already issued.';


// =============================================================================
// Contact Us
// =============================================================================

$string['contactus'] = 'Contact Us';
$string['contactus_submission'] = 'Contact Us submission';
$string['contactus_submission_detail'] = 'Contact Us submission Detail';
$string['contactus_submissions'] = 'Contact Us Submissions';
$string['contactus_submissioncount'] = '{$a} contact form submissions';
$string['contactus_name'] = 'Name';
$string['contactus_email'] = 'Email';
$string['contactus_subject'] = 'Subject';
$string['contactus_message'] = 'Message';
$string['contactus_other'] = 'Other';
$string['filter_name'] = 'Name';

// Table.
$string['submission_id'] = 'ID';
$string['timecreated'] = 'Submitted';
$string['timemodified'] = 'Last modified';
$string['backtosubmissions'] = 'Back to submissions';

// Capabilities.
$string['stablezhelpers:managecontactus'] = 'Manage Contact Us submissions';
$string['stablezhelpers:viewcontactus'] = 'View Contact Us submissions';
$string['stablezhelpers:deletecontactus'] = 'Delete Contact Us submissions';

// Privacy.
$string['privacy:metadata:local_stablezhelpers_contactform'] = 'Stores Contact Us form submissions.';
$string['privacy:metadata:local_stablezhelpers_contactform:userid'] = 'The Moodle user ID of the person who submitted the form.';
$string['privacy:metadata:local_stablezhelpers_contactform:name'] = 'The name provided in the Contact Us form.';
$string['privacy:metadata:local_stablezhelpers_contactform:email'] = 'The email address provided in the Contact Us form.';
$string['privacy:metadata:local_stablezhelpers_contactform:subject'] = 'The subject provided in the Contact Us form.';
$string['privacy:metadata:local_stablezhelpers_contactform:message'] = 'The message provided in the Contact Us form.';
$string['privacy:metadata:local_stablezhelpers_contactform:other'] = 'Other information provided in the Contact Us form.';
$string['privacy:metadata:local_stablezhelpers_contactform:timecreated'] = 'The time when the Contact Us submission was created.';
$string['privacy:metadata:local_stablezhelpers_contactform:timemodified'] = 'The time when the Contact Us submission was last modified.';