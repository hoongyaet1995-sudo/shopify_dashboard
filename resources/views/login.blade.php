<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joshua - Shopify Shop Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html { height: 100%; margin: 0; }
        .full-height { height: 100vh; }
        .login-section { background-color: #ffffff; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; }
        .marketing-section { background-color: #000000; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; text-align: center; }
        .btn-custom { background-color: #2b1eb1; color: white; border-radius: 5px; padding: 10px; width: 100%; }
        .btn-custom:hover { background-color: #1e148a; color: white; }
        .logo-text { font-family: serif; font-size: 4rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 full-height">
        
        <div class="col-md-6 login-section">
            <h1 class="logo-text">Joshua Wan</h1>
            <h4 class="fw-bold">Log In</h4>
            <p class="text-muted small mb-4">Shopify Shop Dashboard</p>

            <form action="/login" method="POST" style="width: 100%; max-width: 350px;">
                @csrf
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label text-muted small" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-custom">Log In</button>
            </form>

            <footer class="mt-5 pt-5 text-muted small">
                Powered by <span class="text-primary">Joshua Wan Workshop</span>
            </footer>
        </div>

        <div class="col-md-6 marketing-section d-none d-md-flex" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('images/finance-elements-frame.jpg') }}'); background-size: cover; background-position: center;">
    
    <h2 class="fw-bold" style="color: #f6ff00;">Start Manage Multiple Shopify Account Today</h2>
    <p class="mt-3 text-light opacity-75 px-5">
        Shopify Marketplace Sync, Orders Management, Customer Relationship Management, Product & Inventory Sync.
    </p>
</div>

    </div>
</div>

</body>
</html>