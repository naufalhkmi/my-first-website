<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>GFM+</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin.css">

  <style>
  .custom-card {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .custom-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
  }
</style>

  

</head>

<body>

  <!-- NAVBAR -->
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



  <!-- MAIN CONTENT -->
  <div class="container mt-4">
    <div class="row">
      <div class="col-md-6">
          <div class="card-body d-flex justify-content-center align-items-center">
            <div class="card custom-card" style="width: 80%;">
              <img src="../assets/Screenshot 2025-06-05 192028.png" class="card-img-top img-fluid" style="object-fit: cover; height: 350px;">
              <div class="card-body">
                <h5 class="card-title text-center" style="font-weight: 644;">Employee Directory</h5>
                <p class="card-text">List of employee that has registered
                </p>
                <a href="listEmployee.php" class="btn btn-primary">Go page</a>
              </div>      </div>
          </div>
      </div>
  
      <div class="col-md-6">
          <div class="card-body d-flex justify-content-center align-items-center">
            <div class="card custom-card" style="width: 80%;">
              <img src="../assets/salam.png" class="card-img-top img-fluid" style="object-fit: cover; height: 350px;" >
              <div class="card-body">
                <h5 class="card-title text-center" style="font-weight: 644;">Add New Employee</h5>
                <p class="card-text"> Complete the form to add a new member to the system.
                </p>
                <a href="addEmployee.php" class="btn btn-primary">Go page</a>
              </div>      </div>
          </div>
      </div>
    </div>
  </div>
  
  

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>