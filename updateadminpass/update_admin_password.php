<?php
// A one-time script to securely update the admin password to a hash.
//
// HOW TO USE:
// 1. Set the correct admin User ID and desired new password below.
// 2. Upload this file to your server.
// 3. Open the file in your browser (e.g., yoursite.com/update_admin_password.php).
// 4. After you see the "SUCCESS" message, DELETE THIS FILE from your server immediately.

include 'config/database.php';

// --- CONFIGURE THIS ---
$adminUserId = 'admin02'; // <-- CHANGE THIS to your admin's exact userID
$newAdminPassword = '133'; // <-- CHANGE THIS to the new password you want

// --- SCRIPT LOGIC (No need to edit below this line) ---

echo "<h1>Updating Admin Password...</h1>";

// Hash the new password
$hashedPassword = password_hash($newAdminPassword, PASSWORD_DEFAULT);

if (!$hashedPassword) {
    die("<h2>ERROR: Could not hash the password. Check your PHP version.</h2>");
}

// Prepare the update statement
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE userID = ?");

if (!$stmt) {
    die("<h2>ERROR: Could not prepare the database statement. Check connection.</h2>" . $conn->error);
}

$stmt->bind_param("ss", $hashedPassword, $adminUserId);

// Execute the update
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<h2>SUCCESS!</h2>";
        echo "<p>The password for admin user '<strong>" . htmlspecialchars($adminUserId) . "</strong>' has been updated.</p>";
        echo "<p>The new hashed password is: " . htmlspecialchars($hashedPassword) . "</p>";
        echo "<p style='color: red; font-weight: bold;'>You can now log in with your new password.</p>";
        echo "<p style='color: red; font-weight: bold;'>DELETE THIS SCRIPT FROM YOUR SERVER NOW!</p>";
    } else {
        echo "<h2>ERROR: User not found.</h2>";
        echo "<p>No user with the ID '<strong>" . htmlspecialchars($adminUserId) . "</strong>' was found in the database. Please check the `\$adminUserId` variable in the script.</p>";
    }
} else {
    echo "<h2>ERROR: Could not execute the update.</h2>" . $stmt->error;
}

$stmt->close();
$conn->close();
?>