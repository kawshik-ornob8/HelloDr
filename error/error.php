<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!--=============== BOXICONS ===============-->
        <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>

        <!--=============== CSS ===============-->
        <link rel="stylesheet" href="assets/css/styles.css">

        <title>Responsive 404 website - Bedimcode</title>
    </head>
    <body>
        <!--==================== HEADER ====================-->
        <header class="header">
            <nav class="nav container">

                <!-- Toggle button -->
                <div class="nav__toggle" id="nav-toggle">
                    <i class='bx bx-grid-alt'></i>
                </div>
            </nav>
        </header>

        <!--==================== MAIN ====================-->
        <main class="main">
            <!--==================== HOME ====================-->
            <section class="home">
                <div class="home__container container">
                    <div class="home__data">
                        <span class="home__subtitle">Error 404</span>
                        <h1 class="home__title">Hey Buddy</h1>
                        <p class="home__description">
                            We can't seem to find the page <br> you are looking for.
                        </p>
                        <a href="../index.php" class="home__button">
                            Go Home
                        </a>
                    </div>

                    <div class="home__img">
                        <img src="assets/img/ghost-img.png" alt="">
                        <div class="home__shadow"></div>
                    </div>
                </div>

                <footer class="home__footer">
                <span>&copy; <?php echo date("Y"); ?> Hello Dr. All Rights Reserved.</span>
                    <span>|</span>
                    <span>contact.hellodr@gmail.com</span>
                    
                </footer>
                
            </section>
        </main>

        <!--=============== SCROLLREVEAL ===============-->
        <script src="assets/js/scrollreveal.min.js"></script>

        <!--=============== MAIN JS ===============-->
        <script src="assets/js/main.js"></script>
    </body>
</html>