<?php
$conn = new mysqli("195.35.61.9", "u337253893_PLPasigSSO", "PLPasigSSO2025", "u337253893_PLPasigSSO");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Manila');

//START - API LIST
$stmt = $conn->prepare("SELECT * FROM api_list");
$stmt->execute();

$result = $stmt->get_result();

$UPLOAD_REQUIREMENTS_IMAGES = 'UPLOAD_REQUIREMENTS_IMAGES';
$UPLOAD_REQUIREMENTS_IMAGES_API = null;

$UPLOAD_REQUIREMENTS_BASE = 'UPLOAD_REQUIREMENTS_BASE_URL';
$UPLOAD_REQUIREMENTS_BASE_URL = null;

$UPDATE_REQUIREMENTS_API = 'UPDATE_REQUIREMENTS_API';
$UPDATE_REQUIREMENTS_API_URL = null;

$PREVIEW_REQUIREMENTS = 'PREVIEW_REQUIREMENTS_URL';
$PREVIEW_REQUIREMENTS_URL = null;

while ($row = $result->fetch_assoc()) {
  if ($row['name'] === $UPLOAD_REQUIREMENTS_IMAGES) {
    $UPLOAD_REQUIREMENTS_IMAGES_API = $row['api_url'];
  }

  if ($row['name'] === $UPLOAD_REQUIREMENTS_BASE) {
    $UPLOAD_REQUIREMENTS_BASE_URL = $row['api_url'];
  }

  if ($row['name'] === $UPDATE_REQUIREMENTS_API) {
    $UPDATE_REQUIREMENTS_API_URL = $row['api_url'];
  }

  if ($row['name'] === $PREVIEW_REQUIREMENTS) {
    $PREVIEW_REQUIREMENTS_URL = $row['api_url'];
  }

  // stop if found
  if (
    $UPLOAD_REQUIREMENTS_IMAGES_API &&
    $UPLOAD_REQUIREMENTS_BASE_URL &&
    $UPDATE_REQUIREMENTS_API_URL &&
    $PREVIEW_REQUIREMENTS_URL
  ) {
    break;
  }
}
//END - API LIST

//START - TEMPLATE LIST
$stmt = $conn->prepare("SELECT * FROM email_template WHERE is_active = 1");
$stmt->execute();

$result = $stmt->get_result();

$TITLE_RESET_PASSWORD = 'Student Support Services - Reset Password';
$SUBJECT_RESET_PASSWORD = null;
$HTML_CODE_RESET_PASSWORD = null;


$TITLE_REGISTER_ACCOUNT = 'Student Support Services - Register Account';
$SUBJECT_REGISTER_ACCOUNT = null;
$HTML_CODE_REGISTER_ACCOUNT = null;

while ($row = $result->fetch_assoc()) {
  // Match titles case-insensitively and trim whitespace to avoid mismatch
  if (strcasecmp(trim($row['title']), trim($TITLE_RESET_PASSWORD)) === 0) {
    $SUBJECT_RESET_PASSWORD = $row['subject'];
    $HTML_CODE_RESET_PASSWORD = $row['html_code'];
  }

  if (strcasecmp(trim($row['title']), trim($TITLE_REGISTER_ACCOUNT)) === 0) {
    $SUBJECT_REGISTER_ACCOUNT = $row['subject'];
    $HTML_CODE_REGISTER_ACCOUNT = $row['html_code'];
  }

  // stop if found
  if (
    $HTML_CODE_RESET_PASSWORD &&
    $HTML_CODE_REGISTER_ACCOUNT
  ) {
    break;
  }
}
//END - TEMPLATE LIST