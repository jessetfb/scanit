<?php
// scan.php
// Staff page for scanning QR codes at checkpoints.

session_start();
require_once 'db.php';

// Only staff can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Get parameters from URL
$assignment_id = $_GET['assignment_id'] ?? null;
$action_type = $_GET['action_type'] ?? 'checkpoint_scan'; // 'clock_in', 'clock_out', 'checkpoint_scan'
$expected_checkpoint_id = $_GET['checkpoint_id'] ?? null; // Only set for clock_in/out actions

$assigned_checkpoint_ids = []; // Stores ID => Name for all checkpoints assigned to the user for this assignment
$assignment_name = "Assigned Checkpoints";

// Fetch valid checkpoint IDs for the current user (either all for their assignments or a specific one)
if ($assignment_id) {
    // If an assignment_id is passed, filter checkpoints for that specific assignment
    $stmt_assignment_name = $pdo->prepare("SELECT name FROM assignments WHERE id = ?");
    $stmt_assignment_name->execute([$assignment_id]);
    $assignment_info = $stmt_assignment_name->fetch();
    if ($assignment_info) {
        $assignment_name = htmlspecialchars($assignment_info['name']);
    } else {
        // Assignment not found or not assigned to user
        $_SESSION['flash_message'] = "Invalid assignment selected.";
        $_SESSION['flash_message_type'] = "danger";
        header("Location: dashboard.php");
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT c.id, c.name FROM checkpoints c
        JOIN assignments a ON c.assignment_id = a.id
        JOIN user_assignments ua ON ua.assignment_id = a.id
        WHERE ua.user_id = ? AND a.id = ?
    ");
    $stmt->execute([$user_id, $assignment_id]);
    $temp_checkpoints = $stmt->fetchAll();

    foreach ($temp_checkpoints as $cp) {
        $assigned_checkpoint_ids[$cp['id']] = $cp['name']; // Store ID => Name for easy lookup
    }

} else {
    // If no specific assignment_id, fetch all checkpoints assigned to the user across all assignments
    // This path shouldn't typically be hit with the new dashboard links, but kept as a fallback.
    $stmt = $pdo->prepare("
        SELECT c.id, c.name
        FROM checkpoints c
        JOIN assignments a ON c.assignment_id = a.id
        JOIN user_assignments ua ON ua.assignment_id = a.id
        WHERE ua.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $temp_checkpoints = $stmt->fetchAll();

    foreach ($temp_checkpoints as $cp) {
        $assigned_checkpoint_ids[$cp['id']] = $cp['name'];
    }
}

// Prepare data for JavaScript
$all_assigned_checkpoints_with_names_json = json_encode($assigned_checkpoint_ids);
$current_action_type_js = json_encode($action_type);
$expected_checkpoint_id_js = json_encode($expected_checkpoint_id ? (int)$expected_checkpoint_id : null);
$assignment_id_js = json_encode($assignment_id ? (int)$assignment_id : null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner | ScanIt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #0d6efd;
            border-radius: 0.5rem;
            overflow: hidden; /* Ensures video stream stays within bounds */
        }
        #reader__dashboard_section_csr {
            display: none; /* Hide the default dashboard, we'll use custom messages */
        }
    </style>
</head>
<body>
    <div class="container mt-4 text-center">
        <h2 class="mb-3">Welcome, <?= htmlspecialchars($username) ?>!</h2>
        <p class="lead mb-4" id="scan_prompt">
            <?php if ($action_type == 'clock_in'): ?>
                Scan "Clock In" QR Code for <?= $assignment_name ?>:
            <?php elseif ($action_type == 'clock_out'): ?>
                Scan "Clock Out" QR Code for <?= $assignment_name ?>:
            <?php else: ?>
                Scan Checkpoints for <?= $assignment_name ?>:
            <?php endif; ?>
        </p>
        
        <div id="reader"></div>
        <div id="result" class="alert mt-3" role="alert" style="display: none;"></div>

        <a href="dashboard.php" class="btn btn-secondary mt-4">&larr; Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
    // Data passed from PHP
    const allAssignedCheckpointsWithNames = <?= $all_assigned_checkpoints_with_names_json ?>; // Object: {id: "name"}
    const currentActionType = <?= $current_action_type_js ?>; // 'clock_in', 'clock_out', 'checkpoint_scan'
    const expectedCheckpointId = <?= $expected_checkpoint_id_js ?>; // Specific ID for clock_in/out
    const currentAssignmentId = <?= $assignment_id_js ?>; // The assignment being patrolled

    const userId = <?= json_encode($user_id) ?>;
    let scanningEnabled = true; // Control flag to prevent multiple rapid scans
    const resultDiv = document.getElementById('result');
    const scanPrompt = document.getElementById('scan_prompt'); // Get the prompt element

    function showMessage(message, type = 'info') {
        resultDiv.style.display = 'block';
        resultDiv.className = `alert mt-3 alert-${type}`;
        resultDiv.innerText = message;
    }

    function onScanSuccess(decodedText) {
        if (!scanningEnabled) return; // Prevent processing if scanning is temporarily disabled

        const scannedCheckpointId = parseInt(decodedText);

        if (isNaN(scannedCheckpointId)) {
            showMessage("❌ Invalid QR code data: Not a valid ID.", 'danger');
            return;
        }

        const scannedCheckpointName = allAssignedCheckpointsWithNames[scannedCheckpointId];

        // --- Client-side Validation Logic ---
        if (!scannedCheckpointName) {
            showMessage("❌ This QR code is not for an assigned checkpoint or is invalid.", 'danger');
            return;
        }

        // Logic based on current action type from dashboard
        let actionToSend = 'checkpoint_scan'; // Default action

        if (currentActionType === 'clock_in') {
            if (scannedCheckpointId !== expectedCheckpointId || scannedCheckpointName !== 'Clock In') {
                showMessage("❌ Please scan the 'Clock In' QR code for this patrol.", 'danger');
                return;
            }
            actionToSend = 'Clock In';
        } else if (currentActionType === 'clock_out') {
            if (scannedCheckpointId !== expectedCheckpointId || scannedCheckpointName !== 'Clock Out') {
                showMessage("❌ Please scan the 'Clock Out' QR code for this patrol.", 'danger');
                return;
            }
            actionToSend = 'Clock Out';
        } else if (currentActionType === 'checkpoint_scan') {
            // Regular checkpoint scan - ensure it's not a special 'Clock In'/'Clock Out' if we are not in that specific flow
            if (scannedCheckpointName === 'Clock In' || scannedCheckpointName === 'Clock Out') {
                showMessage("❌ Please use the 'Start Patrol' or 'End Patrol' buttons on the dashboard for clocking actions.", 'danger');
                return;
            }
            actionToSend = 'checkpoint_scan';
        }

        scanningEnabled = false; // Disable scanning temporarily
        html5QrcodeScanner.pause(); // Pause the scanner to prevent re-scans

        showMessage("Processing scan...", 'info');

        // Send the scanned checkpoint ID, assignment ID, and determined action to the server
        fetch('/scanit/scan_logs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                assignment_id: currentAssignmentId, // Pass the assignment ID
                checkpoint_id: scannedCheckpointId,
                action: actionToSend // Send the determined action
            })
        })
        .then(response => response.json()) // Parse JSON response
        .then(data => {
            if (data.success) {
                // If successful, show message briefly then redirect to dashboard
                showMessage(`✅ ${data.success}`, 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php'; // Redirect to dashboard
                }, 1500); // Give user a moment to see success message
            } else if (data.error) {
                showMessage(`❌ ${data.error}`, 'danger');
                // On error, re-enable scanning after delay
                setTimeout(() => {
                    resultDiv.style.display = 'none';
                    scanningEnabled = true;
                    html5QrcodeScanner.resume(); // Resume the scanner
                }, 3000);
            } else {
                showMessage('An unknown error occurred.', 'danger');
                setTimeout(() => {
                    resultDiv.style.display = 'none';
                    scanningEnabled = true;
                    html5QrcodeScanner.resume(); // Resume the scanner
                }, 3000);
            }
        })
        .catch(err => {
            showMessage("❌ Network error or server unreachable.", 'danger');
            console.error('Fetch error:', err);
            scanningEnabled = true; // Re-enable scanning on error
            html5QrcodeScanner.resume(); // Resume the scanner
        });
    }

    const html5QrcodeScanner = new Html5Qrcode("reader");

    // Request camera permissions and start scanning
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            // Use the first available camera (usually back camera on mobile)
            html5QrcodeScanner.start(
                { facingMode: "environment" }, // Prefer back camera
                { fps: 10, qrbox: { width: 250, height: 250 } }, // Scan settings
                onScanSuccess,
                (errorMessage) => {
                    // console.warn(`QR Code scanning error: ${errorMessage}`); // Optional: log errors
                }
            ).catch(err => {
                showMessage("Camera error: " + err, 'danger');
                console.error("Camera start error:", err);
            });
        } else {
            showMessage("No cameras found on this device.", 'warning');
        }
    }).catch(err => {
        showMessage("Error accessing camera: " + err, 'danger');
        console.error("Get cameras error:", err);
    });
    </script>
</body>
</html>
