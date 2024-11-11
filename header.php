<?php
session_start();
?>

<nav>
    <div class="container nav__container">
        <a href="index.php"><h4>HELLO DR.</h4></a>
        <ul class="nav__menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="doctor lists.php">Appointment</a></li>
            <li><a href="contact.php">Contact</a></li>
            
            <?php if (isset($_SESSION['username'])): ?>
                <!-- Show username and logout option when logged in -->
                <li><a href="#">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <!-- Show login and signup options when logged out -->
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Signup</a></li>
            <?php endif; ?>
        </ul>
        
        <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
        <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
    </div>
</nav>
