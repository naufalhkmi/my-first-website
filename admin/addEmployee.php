<?php
// Include the database connection file. The script will stop if this file is not found.
include '../config/database.php';

// --- FORM SUBMISSION PROCESSING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  // --- 1. Get all the data from the submitted form ---
  $firstName = $_POST['first_name'];
  $lastName = $_POST['last_name'];
  $fullName = $firstName . ' ' . $lastName;
  $dob = $_POST['dob'];
  $phone = $_POST['phone'];
  $employeeIC = $_POST['employee_ic'];
  
  $employeeID = 2025 . rand(100000, 999999); 
  $email = $employeeID . '@staffGFM.my';
  
  $state = $_POST['state'];
  $address = $_POST['address'];
  $fulladdress = $address . ' ' . $state;
  $team = $_POST['team'];
  $role = $_POST['roles'];


  if ($role === 'Leader') {
    $checkLeaderSql = "SELECT COUNT(*) FROM employee WHERE teamID = ? AND roles = 'Leader'";
    $stmt = mysqli_prepare($conn, $checkLeaderSql);
    if ($stmt) {
      mysqli_stmt_bind_param($stmt, "i", $team);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_bind_result($stmt, $leaderCount);
      mysqli_stmt_fetch($stmt);
      mysqli_stmt_close($stmt);

      if ($leaderCount > 0) {
        echo "<script>alert('This team already has a leader. Please select a different role or team.'); window.history.back();</script>";
        exit;
      }
    } else {
      echo "<script>alert('Database Error: Could not prepare statement to check for leader.'); window.history.back();</script>";
      exit;
    }
  }


  $imageName = 'default.jpg'; 


  if (isset($_FILES['picture']) && $_FILES['picture']['error'] === 0) {
    
    $uploadDir = '../uploads/';
    

    $imageFileType = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));


    $allowedTypes = ['jpg', 'png'];


    if (!in_array($imageFileType, $allowedTypes)) {
      echo "<script>alert('Only JPG and PNG files are allowed.'); window.history.back();</script>";
      exit;
    }

    $imageName = preg_replace("/[^a-zA-Z0-9]/", "", $firstName . $lastName) . time() . '.' . $imageFileType;
    $targetFile = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES["picture"]["tmp_name"], $targetFile)) {
      echo "<script>alert('Error uploading the picture.'); window.history.back();</script>";
      exit;
    }
  }

  
  $insertEmployeeSql = "INSERT INTO employee (employeeEmail, employeeID, teamID, roles, employeeFullName, employeeIC, employeeNoPhone, employeeDOB, employeePicture, employeeAddress) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt_employee = mysqli_prepare($conn, $insertEmployeeSql);
  
  mysqli_stmt_bind_param($stmt_employee, "ssisssssss", $email, $employeeID, $team, $role, $fullName, $employeeIC, $phone, $dob, $imageName, $fulladdress);

  if (mysqli_stmt_execute($stmt_employee)) {
    
    $defaultPassword = 'newuser';
    $category = 'employee';
    $hashedDefaultPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    $insertUserSql = "INSERT INTO users (userID, password, category) VALUES (?, ?, ?)";
    $stmt_user = mysqli_prepare($conn, $insertUserSql);
    mysqli_stmt_bind_param($stmt_user, "sss", $employeeID, $hashedDefaultPassword, $category);

    if (mysqli_stmt_execute($stmt_user)) {
      echo "<script>alert('Employee Added Successfully'); window.location.href = 'manageEmployee.php';</script>";
    } else {
      echo "<script>alert('Database Error (Users): " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
    mysqli_stmt_close($stmt_user);

  } else {
    echo "<script>alert('Database Error (Employee): " . mysqli_error($conn) . "'); window.history.back();</script>";
  }
  mysqli_stmt_close($stmt_employee);

}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add employee</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
  
<nav class="navbar bg-light">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-outline-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        ☰
      </button>

      <a class="navbar-brand mb-0" href="#">
        <img src="../assets/download-removebg-preview.png" height="24" alt="Logo">
      </a>
    </div>
    <a class="d-flex align-items-center gap-2 text-decoration-none text-white" href="#">
      <span>Hi Admin</span>
      <img src="../assets/profile-removebg-preview.png" height="32" alt="Profile">
    </a>
  </div>
</nav>

<div class="offcanvas offcanvas-start bg-dark text-white d-flex flex-column" tabindex="-1" id="sidebarMenu"
  style="width:280px;">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column flex-grow-1 p-0">
    <ul class="nav flex-column flex-grow-1">
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="task-management.php">Task Management</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="progress.php">Progress Tracker</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manageEmployee.php">Manage Employee</a>
      </li>
    </ul>
    <div class="mt-auto mb-3 px-3">
      <button class="btn btn-outline-light w-100" data-bs-toggle="modal" data-bs-target="#logoutModal">
        LOG OUT
      </button>
    </div>
  </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to log out?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="../logout.php" class="btn btn-danger">Yes, Logout</a>
      </div>
    </div>
  </div>
</div>


  <div class="container mt-4">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title mb-4 text-center">
              <span style="font-size: 28px;">Fill the details</span>
            </h5>

            <form class="row g-3" id="employeeForm" method="POST" enctype="multipart/form-data">
              <div class="col-md-4">
                <label class="form-label">First name</label>
                <input type="text" class="form-control" name="first_name" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Last name</label>
                <input type="text" class="form-control" name="last_name" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Date of birth</label>
                <input type="date" class="form-control" name="dob" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">IC Number</label>
                <input type="text" class="form-control" name="employee_ic" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Phone number</label>
                <input type="tel" class="form-control" name="phone" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Employee ID</label>
                <input type="text" class="form-control" name="userid" id="userid" readonly placeholder="Will be auto generated">
              </div>

              <div class="col-md-4">
                <label class="form-label">Team</label>
                <select class="form-control" name="team" id="team" required>
                  <option value="" selected disabled>-- Select Team --</option>
                  <option value="1">Team A</option>
                  <option value="2">Team B</option>
                  <option value="3">Team C</option>
                  <option value="4">Team D</option>
                  <option value="5">Team E</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Role</label>
                <select class="form-control" name="roles" id="roles" required>
                  <option value="" selected disabled>-- Select Role --</option>
                  <option value="Leader">Leader</option>
                  <option value="Team Member">Team Member</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Picture</label>
                <input type="file" class="form-control" name="picture" accept=".jpg" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">State</label>
                <input type="text" class="form-control" name="state" required>
              </div>

              <div class="col-9">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="address" required>
              </div>

              <div class="col-12">
                <button class="btn btn-primary" type="submit">Submit form</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
    
    <script>
      // This is a simple client-side validation script. It runs in the user's browser.
      // It waits for the page to be fully loaded before running.
      document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("employeeForm");

        // Listen for the 'submit' event on the form.
        form.addEventListener("submit", function (e) {
          // Find all input fields that have the 'required' attribute.
          const requiredInputs = form.querySelectorAll("[required]");
          let allFilled = true;

          // Loop through each required input.
          requiredInputs.forEach(function (input) {
            // If an input has no value, it's not filled out.
            if (!input.value) {
              allFilled = false;
              input.style.border = "2px solid red"; // Highlight the empty field with a red border.
            } else {
              input.style.border = ""; // Reset the border if it was previously red but is now filled.
            }
          });

          // If any required field was empty...
          if (!allFilled) {
            e.preventDefault(); // ...stop the form from actually submitting to the server.
            alert("Please fill in all the required details."); // And show an alert to the user.
          }
        });
      });
</script>

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>