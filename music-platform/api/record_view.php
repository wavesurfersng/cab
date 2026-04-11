<?php
/**
 * API: Record View and Calculate Earnings
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$contentType = $data['content_type'] ?? '';
$contentId = $data['content_id'] ?? 0;

if (empty($contentType) || empty($contentId)) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

if (!in_array($contentType, ['track', 'video'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid content type']);
    exit;
}

$db = getDBConnection();

// Get content owner
$table = $contentType . 's';
$stmt = $db->prepare("SELECT user_id FROM {$table} WHERE id = ?");
$stmt->execute([$contentId]);
$content = $stmt->fetch();

if (!$content) {
    echo json_encode(['success' => false, 'error' => 'Content not found']);
    exit;
}

$ownerId = $content['user_id'];

// Record view and calculate earnings
$result = recordView($contentType, $contentId, $ownerId);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'View recorded successfully',
        'earnings' => RATE_PER_VIEW
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'View already counted recently'
    ]);
}
?>
