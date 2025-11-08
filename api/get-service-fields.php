<?php
header('Content-Type: application/json');
require_once '../connection/main_connection.php';

// Check if service_id is provided
if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid service ID']);
    exit;
}

$serviceId = (int)$_GET['service_id'];

// Function to fetch service fields from database
function getServiceFields($conn, $serviceId) {
    $fields = [];
    try {
        $stmt = $conn->prepare(
            "SELECT f.field_id, f.label, f.field_type, f.is_required, f.display_order, f.allowed_file_types, f.max_file_size_mb, f.visible_when_option_id
             FROM services_fields f
             WHERE f.service_id = ?
             ORDER BY f.display_order ASC"
        );
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $field = $row;

            // If it's a select, radio, or checkbox field, get the options
            if (in_array($row['field_type'], ['select', 'radio', 'checkbox'])) {
                $optionsStmt = $conn->prepare(
                    "SELECT option_id, option_label, option_value, display_order
                     FROM services_field_options
                     WHERE field_id = ?
                     ORDER BY display_order ASC"
                );
                $optionsStmt->bind_param('i', $row['field_id']);
                $optionsStmt->execute();
                $optionsResult = $optionsStmt->get_result();

                $options = [];
                while ($optionRow = $optionsResult->fetch_assoc()) {
                    $options[] = $optionRow;
                }
                $field['options'] = $options;
            }

            $fields[] = $field;
        }
    } catch (Exception $e) {
        error_log("Error fetching service fields: " . $e->getMessage());
        return false;
    }

    return $fields;
}

// Fetch the fields
$fields = getServiceFields($conn, $serviceId);

if ($fields === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

// Return the fields as JSON
echo json_encode(['fields' => $fields]);
?>