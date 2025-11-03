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