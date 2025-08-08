<?php
require_once '../php/config.php';

// Initialize variables
$name = $email = $password = $confirm_password = '';
$error_message = '';
$success_message = '';

// Check for redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif (strlen($name) < 3) {
        $error_message = 'Name must be at least 3 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!$terms) {
        $error_message = 'You must agree to the Terms & Conditions.';
    } else {
        // Check if email already exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_message = 'Email already exists. Please login or use a different email.';
        } else {
            // Insert new user
            $first_name = explode(' ', $name)[0];
            $last_name = count(explode(' ', $name)) > 1 ? substr(strstr($name, ' '), 1) : '';
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $email, $email, $hashed_password, $first_name, $last_name);
            
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                
                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $first_name;
                
                // If there's a cart in session, associate it with the new user
                $sql = "UPDATE cart SET user_id = ? WHERE session_id = ? AND user_id IS NULL";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "is", $_SESSION['user_id'], $_SESSION['session_id']);
                mysqli_stmt_execute($stmt);
                
                $success_message = 'Account created successfully! Redirecting...';
                
                // Determine where to redirect based on the redirect parameter
                if ($redirect === 'checkout') {
                    header("Refresh: 2; URL=checkout.php");
                } else {
                    header("Refresh: 2; URL=index.php");
                }
            } else {
                $error_message = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png" />
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">MyCling</div>
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">
                <?php if ($redirect === 'checkout'): ?>
                Sign up to complete your purchase
                <?php else: ?>
                Join us and discover amazing fashion
                <?php endif; ?>
            </p>
            
            <?php if (!empty($error_message)): ?>
            <div class="error-message" style="color: #FF6B6B; text-align: center; margin-bottom: 15px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
            <div class="success-message" style="color: #28a745; text-align: center; margin-bottom: 15px;">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <form class="auth-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . ($redirect ? "?redirect=$redirect" : "")); ?>">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms & Conditions</a></label>
                </div>
                
                <button type="submit" class="auth-btn">Create Account</button>
                
                <div class="social-login">
                    <div class="social-login-title">Or sign up with</div>
                    <div class="social-login-buttons">
                        <button type="button" class="social-btn google">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button type="button" class="social-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                    </div>
                </div>
                
                <div class="auth-links">
                    Already have an account? <a href="login.php<?php echo $redirect ? "?redirect=$redirect" : ""; ?>">Login</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Form Validation
        const form = document.querySelector('.auth-form');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        <?php if (empty($success_message)): // Only add client-side validation if not already successful ?>
        form.addEventListener('submit', (e) => {
            let isValid = true;
            
            // Name validation
            if (nameInput.value.length < 3) {
                nameInput.style.borderColor = '#FF6B6B';
                isValid = false;
            } else {
                nameInput.style.borderColor = '';
            }
            
            // Email validation
            if (!emailInput.value.includes('@')) {
                emailInput.style.borderColor = '#FF6B6B';
                isValid = false;
            } else {
                emailInput.style.borderColor = '';
            }
            
            // Password validation
            if (passwordInput.value.length < 6) {
                passwordInput.style.borderColor = '#FF6B6B';
                isValid = false;
            } else {
                passwordInput.style.borderColor = '';
            }
            
            // Confirm password validation
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.style.borderColor = '#FF6B6B';
                isValid = false;
            } else {
                confirmPasswordInput.style.borderColor = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            } else {
                const authBtn = document.querySelector('.auth-btn');
                authBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html> 