<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$user_id = $data['user_id'] ?? null;
$assignment_id = $data['assignment_id'] ?? null;
$checkpoint_id = $data['checkpoint_id'] ?? null;
$action = $data['action'] ?? 'checkpoint_scan';

if (!$user_id || !$assignment_id || !$checkpoint_id || !$action) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data.']);
    exit;
}

// 👉 Fetch checkpoint name from ID (to use as `location`)
$stmt = $pdo->prepare("SELECT name FROM checkpoints WHERE id = ?");
$stmt->execute([$checkpoint_id]);
$checkpoint = $stmt->fetch();

if (!$checkpoint) {
    http_response_code(400);
    echo json_encode(['error' => 'Checkpoint not found.']);
    exit;
}

$location_name = $checkpoint['name'];

// ✅ Insert into logs with correct structure
$stmt = $pdo->prepare("
    INSERT INTO logs (user_id, assignment_id, location, action, timestamp)
    VALUES (?, ?, ?, ?, NOW())
");

try {
    $stmt->execute([$user_id, $assignment_id, $location_name, $action]);
    echo json_encode(['success' => 'Checkpoint logged successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
