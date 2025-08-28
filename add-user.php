<?php
// add-user.php
// Admin page to add new user accounts (staff or admin) with more details and SMS notification.

session_start();
require_once 'db.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = ""; // Initialize message variable

/**
 * Placeholder function for sending SMS.
 * YOU MUST REPLACE THIS WITH ACTUAL SMS API INTEGRATION.
 *
 * @param string $toPhoneNumber The recipient's phone number (e.g., "+254712345678").
 * @param string $textMessage The message content.
 * @return bool True on success, false on failure.
 */
function sendSMS($toPhoneNumber, $textMessage) {
    // --- START SMS API INTEGRATION ---
    // This is a placeholder. You need to integrate with a real SMS API (e.g., Twilio, Vonage, Africa's Talking).

    // Example using a hypothetical SMS service (replace with your actual API calls)
    // You would typically use a library/SDK provided by your SMS service.
    // For Africa's Talking (example, install their SDK via Composer: composer require africastalking/php)
    /*
    require_once __DIR__ . '/vendor/autoload.php'; // Ensure this path is correct for your Composer autoload

    $username = "YOUR_AT_USERNAME"; // Your Africa's Talking username
    $apiKey = "YOUR_AT_API_KEY";   // Your Africa's Talking API Key
    $AT = new AfricasTalking\SDK\AfricasTalking($username, $apiKey);
    $sms = $AT->sms();

    try {
        $result = $sms->send([
            'to'      => $toPhoneNumber,
            'message' => $textMessage,
            'from'    => 'SCANIT' // Your sender ID (optional, but good for branding)
        ]);
        error_log("SMS sent to $toPhoneNumber: " . json_encode($result)); // Log success
        return true;
    } catch (Exception $e) {
        error_log("SMS send failed to $toPhoneNumber: " . $e->getMessage()); // Log error
        return false;
    }
    */

    // For demonstration, we'll just log to the error log and return true
    error_log("SMS Simulation: Sending to $toPhoneNumber - Message: '$textMessage'");
    return true; // Assume success for now
    // --- END SMS API INTEGRATION ---
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $marital_status = $_POST['marital_status'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Basic validation
    if (empty($username) || empty($password) || empty($role) || empty($id_number) || empty($marital_status) || empty($phone_number)) {
        $message = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } elseif (!in_array($marital_status, ['Single', 'Married', 'Divorced', 'Widowed/Widower'])) {
        $message = "Invalid marital status selected.";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $message = "Username already exists. Please choose a different one.";
            } else {
                // Check if ID Number already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id_number = ?");
                $stmt->execute([$id_number]);
                if ($stmt->fetch()) {
                    $message = "ID Number already exists. Please use a different one.";
                } else {
                    // Check if Phone Number already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
                    $stmt->execute([$phone_number]);
                    if ($stmt->fetch()) {
                        $message = "Phone Number already exists. Please use a different one.";
                    } else {
                        // Hash the password securely
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user into the database with new fields
                        $stmt = $pdo->prepare("INSERT INTO users (username, id_number, marital_status, phone_number, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$username, $id_number, $marital_status, $phone_number, $hashed_password, $role])) {
                            $message = "User registered successfully.";

                            // --- Send SMS with credentials ---
                            $sms_message = "Welcome to ScanIt! Your login details: Username: {$username}, Password: {$password}. Keep this safe.";
                            if (sendSMS($phone_number, $sms_message)) {
                                $message .= " Login credentials sent via SMS.";
                            } else {
                                $message .= " Failed to send SMS with credentials. Check server logs.";
                            }
                            // --- End SMS ---

                            // Clear inputs after successful registration
                            $_POST = []; // Clear all POST data to reset form
                        } else {
                            $message = "Failed to add user. Please try again.";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            error_log("Add user database error: " . $e->getMessage()); // Log detailed error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            width: 100%;
            max-width: 500px; /* Slightly wider for more fields */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center mb-4">Add New User</h2>
        <a href="dashboard.php" class="btn btn-secondary btn-sm mb-4">&larr; Back to Dashboard</a>
        <hr class="mb-4">

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="id_number" class="form-label">ID Number:</label>
                <input type="text" name="id_number" id="id_number" value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="marital_status" class="form-label">Marital Status:</label>
                <select name="marital_status" id="marital_status" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="Single" <?= (($_POST['marital_status'] ?? '') == 'Single') ? 'selected' : '' ?>>Single</option>
                    <option value="Married" <?= (($_POST['marital_status'] ?? '') == 'Married') ? 'selected' : '' ?>>Married</option>
                    <option value="Divorced" <?= (($_POST['marital_status'] ?? '') == 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                    <option value="Widowed/Widower" <?= (($_POST['marital_status'] ?? '') == 'Widowed/Widower') ? 'selected' : '' ?>>Widowed/Widower</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number (e.g., +254712345678):</label>
                <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="staff" <?= (($_POST['role'] ?? '') == 'staff') ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-warning w-100">Add User</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
