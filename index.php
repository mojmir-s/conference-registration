<?php
/**
 * Conference Registration System - Landing Page
 */

require_once __DIR__ . '/config/init.php';

$pageTitle = SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1 class="display-4 mb-4">Welcome to the Conference</h1>
        <p class="lead mb-4">Join us for an exciting event featuring world-class speakers, cutting-edge research, and valuable networking opportunities.</p>
        <?php if (isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/pages/user/dashboard.php" class="btn btn-light btn-lg me-2">
                <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/pages/auth/register.php" class="btn btn-light btn-lg me-2">
                <i class="bi bi-person-plus me-2"></i>Register Now
            </a>
            <a href="<?= SITE_URL ?>/pages/auth/login.php" class="btn btn-outline-light btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col">
                <h2>Why Attend?</h2>
                <p class="text-muted">Discover what makes our conference unique</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card">
                    <div class="card-body">
                        <i class="bi bi-mic"></i>
                        <h5>Expert Speakers</h5>
                        <p class="text-muted">Learn from industry leaders and renowned academics sharing their latest insights and discoveries.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card">
                    <div class="card-body">
                        <i class="bi bi-people"></i>
                        <h5>Networking</h5>
                        <p class="text-muted">Connect with peers, researchers, and professionals from around the world.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 feature-card">
                    <div class="card-body">
                        <i class="bi bi-lightbulb"></i>
                        <h5>Innovation</h5>
                        <p class="text-muted">Explore cutting-edge research and emerging trends in the field.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Registration Info -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2>Easy Registration Process</h2>
                <p class="lead">Register in just a few simple steps:</p>
                <ol class="lead">
                    <li class="mb-2">Create your account</li>
                    <li class="mb-2">Verify your email address</li>
                    <li class="mb-2">Complete the registration form</li>
                    <li class="mb-2">Submit your abstract (optional)</li>
                </ol>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Registration Includes:</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Access to all conference sessions
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Conference materials and proceedings
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Networking events and coffee breaks
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Certificate of participation
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Optional accommodation booking
                            </li>
                        </ul>
                        <?php if (!isLoggedIn()): ?>
                            <a href="<?= SITE_URL ?>/pages/auth/register.php" class="btn btn-primary btn-lg w-100">
                                Register Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call for Abstracts -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col">
                <h2>Call for Abstracts</h2>
                <p class="text-muted">Share your research with the community</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h5>Submission Guidelines:</h5>
                        <ul>
                            <li>Abstracts should not exceed <?= ABSTRACT_MAX_WORDS ?> words</li>
                            <li>Include title, authors, and affiliations</li>
                            <li>Provide 3-5 relevant keywords</li>
                            <li>Select your preferred presentation type (oral/poster)</li>
                            <li>Optional: Upload your full paper as PDF</li>
                        </ul>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Authors will be notified of acceptance via email. Accepted abstracts will be included in the conference proceedings.
                        </div>

                        <?php if (isLoggedIn()): ?>
                            <a href="<?= SITE_URL ?>/pages/user/abstract-submit.php" class="btn btn-success btn-lg">
                                <i class="bi bi-file-earmark-plus me-2"></i>Submit Your Abstract
                            </a>
                        <?php else: ?>
                            <p class="mb-0">
                                <a href="<?= SITE_URL ?>/pages/auth/login.php">Login</a> or
                                <a href="<?= SITE_URL ?>/pages/auth/register.php">register</a>
                                to submit your abstract.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-4">Questions?</h2>
        <p class="lead mb-4">We're here to help! Contact us for any inquiries about the conference.</p>
        <a href="mailto:<?= ADMIN_EMAIL ?>" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-envelope me-2"></i><?= ADMIN_EMAIL ?>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
