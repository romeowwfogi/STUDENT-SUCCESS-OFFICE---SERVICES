<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

require_once __DIR__ . '/../connection/main_connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $user_id = intval($_SESSION['user_id']);

    $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
    if ($request_id <= 0) {
        throw new Exception('Invalid request ID');
    }

    // Verify the request belongs to the current user
    $verify = $conn->prepare("SELECT request_id FROM services_requests WHERE request_id = ? AND user_id = ?");
    $verify->bind_param('ii', $request_id, $user_id);
    $verify->execute();
    $verify_res = $verify->get_result();
    if ($verify_res->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    $verify->close();

    // Fetch submitted answers joined with field labels and types per dynamic_services.md
    $stmt = $conn->prepare(
        "SELECT sf.label AS field_label, sf.field_type, sf.display_order, sa.answer_value
         FROM services_answers sa
         JOIN services_fields sf ON sa.field_id = sf.field_id
         WHERE sa.request_id = ?
         ORDER BY sf.display_order ASC"
    );
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $answers = [];
    while ($row = $result->fetch_assoc()) {
        $value = $row['answer_value'];
        $is_file = false;
        $file_url = null;

        // Detect file answers based on field_type and common file path patterns
        if ($row['field_type'] === 'file' || (!empty($value) && (strpos($value, 'uploads/') === 0 || preg_match('/\.(pdf|png|jpg|jpeg|doc|docx)$/i', $value)))) {
            $is_file = true;
            $file_url = $value; // expected to be a web-accessible relative path
        }

        $answers[] = [
            'label' => $row['field_label'],
            'field_type' => $row['field_type'],
            'value' => $value,
            'is_file' => $is_file,
            'file_url' => $file_url
        ];
    }
    $stmt->close();

    echo json_encode(['request_id' => $request_id, 'answers' => $answers]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>