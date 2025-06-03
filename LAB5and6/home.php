<?php
session_start();
require_once 'auth_check.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_name = $is_logged_in ? get_user_display_name() : '';
$login_method = isset($_SESSION['login_method']) ? $_SESSION['login_method'] : 'standard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Management System - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="home-container">
        <div class="home-header">
            <h1><i class="fas fa-book-reader"></i> Library Management System</h1>
            <p>Your gateway to knowledge and discovery</p>
        </div>
        
        <div class="home-content">
            <?php if ($is_logged_in): ?>
                <div class="welcome-message">
                    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>! 
                        <?php if ($login_method === 'google'): ?>
                            <span class="login-method google"><i class="fab fa-google"></i> Google</span>
                        <?php else: ?>
                            <span class="login-method standard"><i class="fas fa-user"></i> Standard</span>
                        <?php endif; ?>
                    </h2>
                    <p>You are currently logged in to the Library Management System. You can now access all the features available to you.</p>
                </div>
            <?php else: ?>
                <div class="welcome-message">
                    <h2>Welcome to our Library Management System</h2>
                    <p>Please log in to access the library resources and manage your borrowed books.</p>
                </div>
            <?php endif; ?>
            
            <div class="card-container">
                <?php if ($is_logged_in): ?>
                    <div class="action-card">
                        <i class="fas fa-books"></i>
                        <h3>Browse Library</h3>
                        <p>Explore our collection of books and resources available for borrowing.</p>
                        <a href="library_test.php" class="btn-action">Go to Library</a>
                    </div>
                    
                    <div class="action-card">
                        <i class="fas fa-user-circle"></i>
                        <h3>My Account</h3>
                        <p>View your profile, borrowed books, and account settings.</p>
                        <a href="profile.php" class="btn-action secondary">View Profile</a>
                    </div>
                    
                    <div class="action-card">
                        <i class="fas fa-sign-out-alt"></i>
                        <h3>Sign Out</h3>
                        <p>Log out from your current session.</p>
                        <a href="logout.php" class="btn-action secondary">Log Out</a>
                    </div>
                <?php else: ?>
                    <div class="action-card">
                        <i class="fas fa-sign-in-alt"></i>
                        <h3>Sign In</h3>
                        <p>Log in with your email and password to access the library.</p>
                        <a href="login.php" class="btn-action">Login with Email</a>
                    </div>
                    
                    <div class="action-card">
                        <i class="fab fa-google"></i>
                        <h3>Google Sign In</h3>
                        <p>Use your Google account for quick and secure access.</p>
                        <a href="google_login.php" class="btn-google">
                            <i class="fab fa-google"></i> Sign in with Google
                        </a>
                    </div>
                    
                    <div class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <h3>Create Account</h3>
                        <p>Don't have an account? Register to join our library.</p>
                        <a href="signup.php" class="btn-action secondary">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> Library Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

