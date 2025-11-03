<?php
$stmt = $conn->prepare("SELECT * FROM general_uploads WHERE status = ?");
$status = 'active';
$stmt->bind_param("s", $status);
$stmt->execute();

$result = $stmt->get_result();

$PLP_LOGO = 'PLP Logo';
$PLP_LOGO_URL = null;

$ADMISSION_BANNER = 'Admission Cover';
$ADMISSION_BANNER_URL = null;

$ID_REPLACEMENT_BANNER = 'ID Replacement Cover';
$ID_REPLACEMENT_BANNER_URL = null;

$GOOD_MORAL_REQUEST_BANNER = 'Goodmoral Cover';
$GOOD_MORAL_REQUEST_BANNER_URL = null;

$FOOTER_IMAGE = 'Footer Image';
$FOOTER_IMAGE_URL = null;

$SYSTEM_LOGO = 'System Logo';
$SYSTEM_LOGO_URL = null;

$SSO_LOGO = 'SSO Logo';
$SSO_LOGO_URL = null;

$APPLICANT_BACKGROUND = 'Admission Applicant Background';
$APPLICANT_BACKGROUND_URL = null;

$CONTACT_US_BANNER = 'Contact Us Banner';
$CONTACT_US_BANNER_URL = null;

$BANNER_HEADER = 'Banner Header';
$BANNER_HEADER_URL = null;

while ($row = $result->fetch_assoc()) {
    if ($row['title'] === $PLP_LOGO) {
        $PLP_LOGO_URL = $row['file_url'];
    }

    if ($row['title'] === $ADMISSION_BANNER) {
        $ADMISSION_BANNER_URL = $row['file_url'];
    }

    if ($row['title'] === $ID_REPLACEMENT_BANNER) {
        $ID_REPLACEMENT_BANNER_URL = $row['file_url'];
    }

    if ($row['title'] === $GOOD_MORAL_REQUEST_BANNER) {
        $GOOD_MORAL_REQUEST_BANNER_URL = $row['file_url'];
    }

    if ($row['title'] === $FOOTER_IMAGE) {
        $FOOTER_IMAGE_URL = $row['file_url'];
    }

    if ($row['title'] === $SYSTEM_LOGO) {
        $SYSTEM_LOGO_URL = $row['file_url'];
    }

    if ($row['title'] === $SSO_LOGO) {
        $SSO_LOGO_URL = $row['file_url'];
    }

    if ($row['title'] === $APPLICANT_BACKGROUND) {
        $APPLICANT_BACKGROUND_URL = $row['file_url'];
    }

    if ($row['title'] === $CONTACT_US_BANNER) {
        $CONTACT_US_BANNER_URL = $row['file_url'];
    }

    if ($row['title'] === $BANNER_HEADER) {
        $BANNER_HEADER_URL = $row['file_url'];
    }

    // stop if found
    if (
        $PLP_LOGO_URL &&
        $ADMISSION_BANNER_URL &&
        $ID_REPLACEMENT_BANNER_URL &&
        $GOOD_MORAL_REQUEST_BANNER_URL &&
        $FOOTER_IMAGE_URL &&
        $SSO_LOGO_URL &&
        $SYSTEM_LOGO_URL &&
        $APPLICANT_BACKGROUND_URL &&
        $CONTACT_US_BANNER_URL &&
        $BANNER_HEADER_URL
    ) {
        break;
    }
}
