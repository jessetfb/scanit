<?php
// generate-qrs.php
session_start();
require_once 'db.php';

// Composer autoloader
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Composer autoload not found. Run <code>composer install</code> first.");
}

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get assignment ID safely
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : null;
if (!$assignment_id) {
    header("Location: dashboard.php?error=no_assignment_id_for_qrs");
    exit;
}

// Fetch assignment name for display
$stmt_assignment = $pdo->prepare("SELECT name FROM assignments WHERE id = ?");
$stmt_assignment->execute([$assignment_id]);
$assignment = $stmt_assignment->fetch();
if (!$assignment) {
    header("Location: dashboard.php?error=assignment_not_found_for_qrs");
    exit;
}
$assignment_name = htmlspecialchars($assignment['name']);

// Fetch checkpoints for this assignment
$stmt_checkpoints = $pdo->prepare("SELECT id, name FROM checkpoints WHERE assignment_id = ? ORDER BY name ASC");
$stmt_checkpoints->execute([$assignment_id]);
$checkpoints = $stmt_checkpoints->fetchAll();

// Prepare QR codes array
$qrCodes = [];

foreach ($checkpoints as $checkpoint) {
    // Embed only the checkpoint ID in the QR code.
    $data_to_encode = (string)$checkpoint['id'];

try {
    $builder = new Builder(
        writer: new PngWriter(),
        data: $data_to_encode,
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10
    );

    $result = $builder->build();

    $imageData = base64_encode($result->getString());
    $qrCodes[] = [
        'checkpoint_name' => $checkpoint['name'],
        'image' => $imageData
    ];

} catch (Throwable $e) {
    error_log('Error generating QR for checkpoint ID ' . $checkpoint['id'] . ': ' . $e->getMessage());
    $qrCodes[] = [
        'checkpoint_name' => $checkpoint['name'],
        'image' => null,
        'error' => 'Failed to generate QR code. (' . htmlspecialchars($e->getMessage()) . ')'
    ];
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes for Assignment: <?= $assignment_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .qr-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            text-align: center;
        }
        .qr-card img {
            max-width: 100%;
            height: auto;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3">QR Codes for Assignment: <span class="text-primary"><?= $assignment_name ?></span></h2>
        <a href="manage_assignments.php" class="btn btn-secondary btn-sm mb-4">&larr; Back to Manage Assignments</a>
        <hr class="mb-4">

        <?php if (empty($qrCodes)): ?>
            <div class="alert alert-info" role="alert">
                No checkpoints found for this assignment. Please add checkpoints first.
                <a href="add-checkpoints.php?assignment_id=<?= $assignment_id ?>" class="alert-link">Add Checkpoints</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($qrCodes as $qr): ?>
                    <div class="col">
                        <div class="qr-card">
                            <h5 class="mb-3 text-break"><?= htmlspecialchars($qr['checkpoint_name']) ?></h5>
                            <?php if ($qr['image']): ?>
                                <img src="data:image/png;base64,<?= $qr['image'] ?>" alt="QR Code for <?= htmlspecialchars($qr['checkpoint_name']) ?>" class="img-fluid">
                            <?php else: ?>
                                <div class="alert alert-warning" role="alert">
                                    <?= htmlspecialchars($qr['error'] ?? 'QR code not available.') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <div class="d-flex justify-content-center align-items-center mt-4">
                <div class="alert alert-info mb-0 me-3" role="alert">
                    <i class="fas fa-info-circle me-2"></i> Print these QR codes and place them at their respective checkpoints.
                </div>
                <button class="btn btn-primary btn-lg" onclick="window.print()">
                    <i class="fas fa-print me-2"></i> Print All QR Codes
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>