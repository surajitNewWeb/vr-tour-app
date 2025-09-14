<?php
// 404.php
http_response_code(404);
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <h1 class="display-1 text-primary">404</h1>
                <h2>Page Not Found</h2>
                <p class="lead">The page you're looking for doesn't exist.</p>
                <a href="index.php" class="btn btn-primary">Go Home</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>