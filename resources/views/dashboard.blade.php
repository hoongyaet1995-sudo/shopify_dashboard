<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - {{ $customer->customers_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // 1. Load the form into the modal
            $('#connectStoreModal').on('show.bs.modal', function () {
                $.get('/merchants/create', function(html) {
                    $('#modal-body-content').html(html);
                });
            });
            $('#connectStoreModal').on('hidden.bs.modal', function () {
                $(this).find('#modal-body-content').html('<div class="text-center p-3"><div class="spinner-border text-primary"></div><p class="mt-2">Loading form...</p></div>');
            });

            // 2. Submit the form via AJAX
            $(document).on('submit', '#storeConnectForm', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '/merchants/store',
                    type: 'POST',
                    data: $(this).serialize(),
                   success: function(response) {
                        if (response.redirect_url) {
                            // This is the magic line that actually takes you to Shopify
                            window.location.href = response.redirect_url;
                        } else {
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        // This will show you the real error message from Laravel
                        console.log(xhr.responseText);
                        alert('Error: ' + xhr.status + ' - ' + xhr.responseJSON.message);
                    }
                });
            });
        });

        function authorizeStore(storeId) {
            $.ajax({
                url: "{{ url('/merchants/authorize') }}",
                method: "POST",
                data: {
                    store_id: storeId,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function (response) {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert(xhr.responseJSON?.message ?? 'Something went wrong');
                }
            });
        }
    </script>
    <style>
        body { background-color: #f8f9fa; }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #fff;
            border-right: 1px solid #e0e0e0;
            padding: 20px;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: #333;
            font-weight: 500;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link i { margin-right: 15px; font-size: 1.2rem; }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #f0f0ff;
            color: #2b1eb1;
        }

        .sidebar .nav-link.active i { color: #2b1eb1; }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 40px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding-left: 10px;
        }

        .logo-circle {
            width: 40px;
            height: 40px;
            border: 1px solid #000;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: serif;
            font-weight: bold;
            margin-right: 10px;
        }

        /* Card Styling for the Table area */
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            padding: 25px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-section">
        <div class="logo-circle">{{ strtoupper(substr($customer->customers_name, 0, 1)) }}</div>
        <span class="fw-bold fs-4">{{ $customer->customers_name }}</span>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link" href="#"><i class="bi bi-house-door"></i> Home</a>
        <a class="nav-link" href="#"><i class="bi bi-people"></i> Customers</a>
        <a class="nav-link" href="#"><i class="bi bi-card-list"></i> Orders</a>
        <a class="nav-link active" href="#"><i class="bi bi-shop"></i> Marketplace</a>
        <a class="nav-link" href="#"><i class="bi bi-graph-up"></i> Report</a>
        <a class="nav-link" href="#"><i class="bi bi-gear"></i> Setting</a>
    </nav>

    <form action="/logout" method="POST" class="mt-5 pt-5">
        @csrf
        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-danger">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </button>
    </form>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="fw-bold mb-4">Marketplace Merchant</h2>

        <div class="alert alert-light border-start border-primary border-4 shadow-sm mb-4" role="alert">
            <span class="text-muted">
                One merchant account can connect to one Shopee, Tiktok and Lazada Seller.
                You can also connect to multiple seller accounts to the same platform.
                <span class="badge bg-light text-dark border">This has to be approved by your service provider.</span>
            </span>
        </div>

        <div class="dashboard-card">
            <table class="table align-middle">
                <thead class="text-muted small">
                    <tr>
                        <th>Merchant Name</th>
                        <th class="text-center">Platform</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                   @foreach($merchants as $merchant)
                    <tr>
                        <td>{{ $merchant->marketplace_user_name }}</td>
                        <td class="text-center">
                            <span class="badge bg-danger">S</span>
                            <span class="badge bg-dark">T</span>
                            <span class="badge bg-secondary">L</span>
                        </td>
                        <td class="text-end">
                            <!--<i class="bi bi-three-dots" style="cursor: pointer;"></i>-->
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                onclick="authorizeStore({{ $merchant->marketplace_user_id }})">
                                Authorize Now
                            </button>

                        </td>
                    </tr>
                    @endforeach

                @if($merchants->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <p class="text-muted">No merchants connected yet.</p>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#connectStoreModal">
                                    <i class="bi bi-plus-lg"></i> Connect Store Now
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <span class="badge bg-light text-dark border p-2">
                Total {{ $merchants->count() }} company
            </span>
        </div>
    </div>
</div>
<div class="modal fade" id="connectStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Connect New Store</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-body-content" class="modal-body">
                <div class="text-center p-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
