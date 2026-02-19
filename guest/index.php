<?php
$pageTitle = 'Cloud 9 Cafe | Home';
require_once __DIR__ . '/../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-light sticky-top cafe-navbar" aria-label="Primary navigation">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#home">
            <span class="brand-mark">C9</span>
            <span class="brand-text">Cloud 9 Cafe</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar" aria-controls="guestNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="guestNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#featured-menu">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="#about-preview">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#site-footer">Contact</a></li>
                <li class="nav-item"><a class="nav-link nav-action" href="/cloud_9_cafe/guest/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-cafe-primary btn-sm" href="/cloud_9_cafe/guest/auth/register.php">Register</a></li>
            </ul>
        </div>
    </div>
</nav>

<header id="home" class="hero-banner">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 hero-copy">
                <span class="hero-eyebrow">Cloud 9 Cafe Experience</span>
                <h1 class="hero-title">Handcrafted coffee, warm pastries, and moments worth savoring.</h1>
                <p class="hero-subtitle">Step into a calm corner of the city where every cup is brewed with care and every bite feels like home.</p>
                <div class="hero-actions d-flex flex-wrap gap-3">
                    <a href="#featured-menu" class="btn btn-cafe-primary btn-lg">Explore Menu</a>
                    <a href="/cloud_9_cafe/guest/about.php" class="btn btn-cafe-outline btn-lg">Our Story</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-panel shadow-lg">
                    <h2 class="panel-title">Today at Cloud 9</h2>
                    <ul class="hero-list list-unstyled mb-0">
                        <li><i class="bi bi-cup-hot"></i> Signature House Blend</li>
                        <li><i class="bi bi-clock"></i> Open Daily: 8:00 AM - 10:00 PM</li>
                        <li><i class="bi bi-geo-alt"></i> 42 Brew Lane, Downtown</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="featured-menu" class="section-space featured-menu">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-kicker">Guest Favorites</span>
            <h2>Featured Menu Items</h2>
            <p>Small-batch ingredients, rich flavors, and cafe classics crafted fresh every day.</p>
        </div>

        <div class="row g-4 card-stagger">
            <div class="col-md-6 col-xl-4">
                <article class="menu-card h-100">
                    <div class="menu-card-media media-espresso"></div>
                    <div class="menu-card-body">
                        <h3>Caramel Cloud Latte</h3>
                        <p>Velvety espresso layered with steamed milk and caramel drizzle.</p>
                        <div class="menu-meta">
                            <span>$6.50</span>
                            <a href="/cloud_9_cafe/guest/menu.php">View More</a>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-md-6 col-xl-4">
                <article class="menu-card h-100">
                    <div class="menu-card-media media-mocha"></div>
                    <div class="menu-card-body">
                        <h3>Hazelnut Mocha</h3>
                        <p>Dark cocoa, toasted hazelnut, and espresso finished with light foam.</p>
                        <div class="menu-meta">
                            <span>$7.00</span>
                            <a href="/cloud_9_cafe/guest/menu.php">View More</a>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-md-6 col-xl-4 mx-md-auto mx-xl-0">
                <article class="menu-card h-100">
                    <div class="menu-card-media media-dessert"></div>
                    <div class="menu-card-body">
                        <h3>Butter Croissant Set</h3>
                        <p>Fresh-baked croissant served with a choice of drip coffee or tea.</p>
                        <div class="menu-meta">
                            <span>$5.25</span>
                            <a href="/cloud_9_cafe/guest/menu.php">View More</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section id="about-preview" class="section-space about-preview">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="about-card">
                    <p class="about-overline">About Cloud 9 Cafe</p>
                    <h2>Rooted in quality, designed for comfort.</h2>
                    <p>Cloud 9 Cafe started with one goal: create a welcoming place where premium coffee and honest food meet relaxed hospitality.</p>
                    <ul class="about-list list-unstyled">
                        <li><i class="bi bi-check2-circle"></i> Ethically sourced coffee beans</li>
                        <li><i class="bi bi-check2-circle"></i> Freshly baked pastries every morning</li>
                        <li><i class="bi bi-check2-circle"></i> Cozy space for work, friends, and family</li>
                    </ul>
                    <a href="/cloud_9_cafe/guest/about.php" class="btn btn-cafe-primary">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-highlight">
                    <div class="highlight-grid">
                        <div>
                            <strong>9+</strong>
                            <span>Years serving coffee lovers</span>
                        </div>
                        <div>
                            <strong>40+</strong>
                            <span>Handcrafted menu choices</span>
                        </div>
                        <div>
                            <strong>4.8/5</strong>
                            <span>Average guest satisfaction</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer id="site-footer" class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5>Cloud 9 Cafe</h5>
                <p>Your daily destination for handcrafted coffee and fresh comfort food.</p>
            </div>
            <div class="col-lg-4">
                <h5>Contact</h5>
                <p><i class="bi bi-geo-alt"></i> 42 Brew Lane, Downtown</p>
                <p><i class="bi bi-telephone"></i> +1 (555) 019-9090</p>
                <p><i class="bi bi-envelope"></i> hello@cloud9cafe.com</p>
            </div>
            <div class="col-lg-4">
                <h5>Follow Us</h5>
                <div class="social-links">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
        <hr>
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <small>&copy; <span id="currentYear">2026</span> Cloud 9 Cafe. All rights reserved.</small>
            <small><a href="/cloud_9_cafe/guest/contact.php">Send us a message</a></small>
        </div>
    </div>
</footer>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
