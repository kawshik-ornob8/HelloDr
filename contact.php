<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Hello Dr.</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="stylesheet" href="./css/contact.css">
        <link rel="icon" type="image/x-icon" href="./images/favicon.png">
        


        <!--ICONSCOUT CDN-->
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
        <!--Google Fonts (MOntserrat)-->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,800;0,900;1,600&display=swap" rel="stylesheet">
        <style>
            body {
                background-image: url("./images/bg-texture.png");
            }
        </style>   
        <link rel="icon" href="./images/favicon.png" type="image/png">
        <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
    </head>
    <body>
        <!--=====NAVBAR======-->
        <nav>
            <div class="container nav__container">
                <a href="index.html"><h4>HELLO DR.</h4></a>
                <ul class="nav__menu">
                    <li><a href="index">Home</a></li>
                    <li><a href="about">About</a></li> 
                    <li><a href="doctor_lists">Appointment</a></li>
                    <li><a href="contact">Contact</a></li>
                </ul>
                <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
                <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>

            </div>
        </nav>
        <!--=====END OF NAVBAR======-->




        <!--=====Contact======-->
        <section class="contact">
            <div class="container contact__container">
                <aside class="contact__aside">
                    <div class="aside__image">
                        <img src="./images/contact.svg">
                    </div>
                    <h2>Contact us</h2>
                    <p>
                        At Your Doctors Online, we provide easily accessible virtual health care for your families around the clock with the help of our board-certified online doctors. We believe that everyone in the world should have the ability to connect with an experienced doctor online.
                    </p>
                    <ul class="contact__details">
                        <li>
                            <i class="uil uil-phone-times"></i>
                            <h5>+8801975288108</h5>
                        </li>
                        <li>
                            <i class="uil uil-envelope"></i>
                            <h5>contact.hellodr@gmail.com</h5> 
                        </li>
                        <li>
                            <i class="uil uil-location-point"></i>
                            <h5>Dhaka, Bangladesh</h5>
                        </li>
                    </ul>
                    <ul class="contact__socials">
                        <li>
                            <a href="https://www.instagram.com/iam_kawshik/" target="_blank"><i class="uil uil-instagram"></i></a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/kawshik.ornob.01" target="_blank"><i class="uil uil-facebook-f"></i></a>
                        </li>
                        <li>
                            <a href="https://twitter.com/iam_kawshik" target="_blank"><i class="uil uil-twitter-alt"></i></i></a>
                        </li>
                    </ul>
                </aside>


                <form action="https://formspree.io/f/mldeoadq" method="POST" class="contact__form">
                    <div class="form__name">
                        <input type="text" name="First Name" placeholder="First Name" required>
                        <input type="text" name="Last Name" placeholder="Last Name" required>
                    </div>
                    <input type="email" name="Email Address" placeholder="Enter Email Address" required>
                    <textarea name="Message" rows="7" placeholder="Message" required></textarea>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </section>








        <!--=====Footer======-->
        <footer>
            <div class="container footer__container">
                <div class="footer__1">
                    <a href="index.html" class="footer__logo"><h4>HELLO DR.</h4></a>
                    <p>
                        At Your Doctors Online, we provide easily accessible virtual health care for your families around the clock with the help of our board-certified online doctors. We believe that everyone in the world should have the ability to connect with an experienced doctor online.
                    </p>
                </div>
                <div class="footer__2">
                    <h4>Permalink</h4>
                    <ul class="permalink">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="appointment.php">Appointment</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer__3">
                    <h4>Privacy</h4>
                    <ul class="privacy">
                        <li><a href="privacy-policy.php">Privacy Policy</a></li>
                        <li><a href="terms-and-conditions.php">Terms and Conditions</a></li>
                        <li><a href="refund-policy.php">Refund Policy</a></li>
                    </ul>
                </div>

                <div class="footer__4">
                    <h4>Contact us</h4>
                    <div>
                        <p>+8801975288108</p>
                        <p>contact.hellodr@gmail.com</p>
                    </div>
                    <ul class="footer__socials">
                        <li><a href="https://www.facebook.com/kawshik.ornob.01"><i class="uil uil-facebook-f"></i></a></li>
                        <li><a href="https://www.instagram.com/iam_kawshik/"><i class="uil uil-instagram-alt"></i></a></li>
                        <li><a href="https://www.twitter.com/iam_kawshik/"><i class="uil uil-twitter"></i></a></li>
                    </ul>
                </div>
                
            </div>
            <div class="footer__copyright">
                <small>Copyright &copy; Hello Dr.  All Rights Reserved.</small>
            </div>
        </footer>
        <!--=====End Footer======-->
        <script src="./main.js"></script>
    
    </body>
</html>