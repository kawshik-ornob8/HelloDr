<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<nav>
    <div class="container nav__container">
        <a href="index"><h4>HELLO DR.</h4></a>
        <ul class="nav__menu">
            <li><a href="index">Home</a></li>
            <li><a href="about">About</a></li>
            <li><a href="doctor_lists">Appointment</a></li>
            <li><a href="contact">Contact</a></li>
            
            <?php if (isset($_SESSION['username'])): ?>
                <!-- Show username and logout option when logged in -->
                <li><a href="user/user_profile">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                <li><a href="logout">Logout</a></li>
            <?php else: ?>
                <!-- Show login and signup options when logged out -->
                <li><a href="login">Login</a></li>
                <li><a href="signup">Signup</a></li>
            <?php endif; ?>
        </ul>
        
        <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
        <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
    </div>
</nav>
