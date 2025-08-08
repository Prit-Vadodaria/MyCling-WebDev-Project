<?php
require_once '../php/config.php';
require_once '../php/cart_functions.php';

// Get cart summary for header if needed later
$cart_summary = getCartSummary();
$total_items = $cart_summary['total_items'];

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $time = isset($_POST['time']) ? $_POST['time'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : 'any';
    $venue = isset($_POST['venue']) ? $_POST['venue'] : '';
    $user_id = getCurrentUserId();
    $session_id = getSessionId();

    if (empty($date) || empty($time) || empty($venue)) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Insert appointment into database
        $sql = "INSERT INTO appointments (user_id, session_id, appointment_date, appointment_time, preferred_gender, venue, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssss", $user_id, $session_id, $date, $time, $gender, $venue);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = 'Your appointment has been booked successfully!';
        } else {
            $error_message = 'There was an error booking your appointment. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment - MyCling | Modern Fashion E-Commerce</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body class="appointment-page">

    <div class="form-container">
        <h2>Book an Appointment</h2>
        
        <?php if (!empty($success_message)): ?>
        <div class="success-message" style="color: green; text-align: center; margin-bottom: 15px;">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
        <div class="error-message" style="color: red; text-align: center; margin-bottom: 15px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="date">Select Date:</label>
            <input type="date" id="date" name="date" required>

            <label for="time">Select Time:</label>
            <input type="time" id="time" name="time" required>

            <label for="gender">Preferred Gender:</label>
            <select id="gender" name="gender">
                <option value="any">Any</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>

            <label for="venue">Venue:</label>
            <input type="text" id="venue" name="venue" placeholder="Enter location" required>

            <button type="submit" class="submit-btn"><i class="fas fa-check-circle"></i> Confirm Appointment</button>
        </form>
        <div class="auth-links" style="margin-top: 20px;">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').min = today;
            
            <?php if (!empty($success_message)): ?>
            // Redirect after successful booking
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html> 