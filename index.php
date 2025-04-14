<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Notes - Personal Note Keeper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <h1 class="display-4 mb-4">Nexux Notes</h1>
                <p class="lead mb-5">Your Personal Note Keeper</p>
                
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Welcome to Nexus Notes</h2>
                        <p class="card-text mb-4">Organize your thoughts, ideas, and important information in one place.</p>
                        
                        <div class="d-grid gap-3">
                            <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                            <a href="signup.php" class="btn btn-outline-primary btn-lg">Sign Up</a>
                        </div>
                    </div>
                </div>

                <div class="features mt-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="fas fa-folder-open"></i>
                                <h4>Organize</h4>
                                <p>Create folders and tags</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="fas fa-search"></i>
                                <h4>Search</h4>
                                <p>Find notes instantly</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-item">
                                <i class="fas fa-moon"></i>
                                <h4>Dark Mode</h4>
                                <p>Easy on the eyes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html> 