@extends('layouts.app')

@section('title', 'Orders - Shopify Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">Orders</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-light border btn-sm px-3" id="btn-sync-orders">
                <i class="bi bi-arrow-clockwise me-1"></i> Retrieve Latest Order
            </button>

            <button class="btn btn-light border btn-sm px-3">Export</button>
            <div class="dropdown">
                <button class="btn btn-light border btn-sm dropdown-toggle px-3" data-bs-toggle="dropdown">
                    More actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Archive orders</a></li>
                    <li><a class="dropdown-item" href="#">Unarchive orders</a></li>
                </ul>
            </div>
            <button class="btn btn-dark btn-sm px-3">Create order</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="dropdown h-100">
                <div class="card border-0 shadow-sm p-3 h-100 dropdown-toggle custom-date-trigger"
                    data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <div class="text-muted small mb-1">
                        <i class="bi bi-calendar3 me-2"></i><span id="selected-date-text">Today</span>
                    </div>
                </div>
                <ul class="dropdown-menu shadow-lg border-0 p-2 mt-2" style="min-width: 320px; border-radius: 12px;">
                    <li>
                        <label class="dropdown-item rounded-3 p-3 d-flex align-items-start gap-3">
                            <input class="form-check-input" type="radio" name="date_filter" value="today" {{ $days == 0 ? 'checked' : '' }}>
                            <div>
                                <div class="fw-bold">Today</div>
                                <div class="small text-muted">Compared to yesterday up to current hour</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <label class="dropdown-item rounded-3 p-3 d-flex align-items-start gap-3">
                            <input class="form-check-input" type="radio" name="date_filter" value="last_7" {{ $days == 7 ? 'checked' : '' }}>
                            <div>
                                <div class="fw-bold">Last 7 days</div>
                                <div class="small text-muted">Compared to the previous 7 days</div>
                            </div>
                        </label>
                    </li>
                    <li>
                        <label class="dropdown-item rounded-3 p-3 d-flex align-items-start gap-3">
                            <input class="form-check-input" type="radio" name="date_filter" value="last_30" {{ $days == 30 ? 'checked' : '' }}>
                            <div>
                                <div class="fw-bold">Last 30 days</div>
                                <div class="small text-muted">Compared to the previous 30 days</div>
                            </div>
                        </label>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small mb-1">Orders</div>
                <div class="fs-4 fw-bold">{{ $marketplace_order->count() }} <span class="text-muted fs-6 fw-normal">—</span></div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small mb-1">Items ordered</div>
                <div class="fs-4 fw-bold">{{ $marketplace_order->sum(function($order) { return $order->products->sum('quantity_purchase'); }) }} <span class="text-muted fs-6 fw-normal">—</span></div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small mb-1">Total Sales</div>
                <div class="fs-4 fw-bold">${{ $marketplace_order->sum('total_price') }} <span class="text-muted fs-6 fw-normal">—</span></div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small mb-1">Orders fulfilled</div>
                <div class="fs-4 fw-bold">{{ $marketplace_order->where('fulfillment_status', 'FULFILLED')->count() }} <span class="text-muted fs-6 fw-normal">—</span></div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="text-muted small mb-1">Orders delivered</div>
                <div class="fs-4 fw-bold">0 <span class="text-muted fs-6 fw-normal">—</span></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-3 px-3">
            <div class="d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs border-0 custom-tabs">
                    <li class="nav-item"><a href="{{ route('marketplace-order', ['status' => 'all', 'range' => $days]) }}" class="nav-link {{ $status === 'all' ? 'active fw-bold' : 'text-muted' }}"> All</a></li>
                    <li class="nav-item"><a href="{{ route('marketplace-order', ['status' => 'unfulfilled', 'range' => $days]) }}" class="nav-link {{ $status === 'unfulfilled' ? 'active fw-bold' : 'text-muted' }}">Unfulfilled</a></li>
                    <li class="nav-item"><a href="{{ route('marketplace-order', ['status' => 'unpaid', 'range' => $days]) }}" class="nav-link {{ $status === 'unpaid' ? 'active fw-bold' : 'text-muted' }}">Unpaid</a></li>
                    <li class="nav-item"><a href="{{ route('marketplace-order', ['status' => 'open', 'range' => $days]) }}" class="nav-link {{ $status === 'open' ? 'active fw-bold' : 'text-muted' }}">Open</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Archived</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#"><i class="bi bi-plus"></i></a></li>
                </ul>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light border"><i class="bi bi-search"></i></button>
                    <button class="btn btn-sm btn-light border"><i class="bi bi-filter"></i></button>
                    <button class="btn btn-sm btn-light border"><i class="bi bi-sort-down"></i></button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-muted text-uppercase">
                        <th style="width: 30px;">#</th>
                        <th style="width: 40px;"><input type="checkbox" class="form-check-input"></th>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Channel</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Fulfillment</th>
                        <th>Items</th>
                        <th>Shipping Fee</th>
                        <th>Shipping Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marketplace_order as $order)
                    <tr>
                        <td class="text-muted small">{{ $order->id }}</td>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td class="fw-bold">{{ $order->marketplace_invoice_id }}</td>
                        <td class="small text-nowrap">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, h:i a') }}</td>
                        <td class="text-muted small">
                            @if($order->customer_name || $order->customer_email)
                                <div class="fw-semibold text-dark">
                                    {{ $order->customer_name ?? 'Guest Customer' }}
                                </div>
                                <div class="text-muted small">
                                    {{ $order->customer_email }}
                                </div>
                            @else
                                <span class="text-muted fst-italic">No customer info</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border fw-normal">
                                {{ getMarketplaceShopName($order->user_id, $order->marketplace_user_id) }}
                            </span>
                        </td>
                        <td class="fw-bold">${{ number_format($order->total_price, 2) }}</td>

                        <td>
                            <span class="badge rounded-pill {{ getFinancialStatusClass($order->financial_status) }} fw-normal px-2 py-1">
                                <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                {{ ucfirst(strtolower($order->financial_status)) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge rounded-pill {{ getFulfillmentStatusClass($order->fulfillment_status) }} fw-normal px-2 py-1">
                                {{ ucfirst(strtolower($order->fulfillment_status)) }}
                            </span>
                        </td>

                        <td class="small text-muted text-nowrap">
                            <div class="dropdown">
                                <a class="text-muted text-decoration-none dropdown-toggle cursor-pointer"
                                href="#"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                    {{ $order->products->sum('quantity_purchase') }}
                                    {{ Str::plural('item', $order->products->sum('quantity_purchase')) }}
                                </a>

                                <div class="dropdown-menu shadow border-0 p-3" style="min-width: 300px; border-radius: 12px;">
                                    <div class="mb-2">
                                        <span class="badge rounded-pill {{ getFulfillmentStatusClass($order->fulfillment_status) }} fw-normal px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                            {{ ucfirst(strtolower($order->fulfillment_status)) }}
                                        </span>
                                    </div>

                                    @foreach($order->products as $product)
                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded-3 mb-1">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $product->product_image ?? asset('images/no-image.png') }}"
                                                    alt="product"
                                                    style="width: 40px; height: 40px; object-fit: cover;"
                                                    class="rounded border me-3">
                                                <span class="text-dark small fw-medium text-truncate" style="max-width: 150px;">
                                                    {{ $product->product_name }}
                                                </span>
                                            </div>
                                            <div class="text-muted small">
                                                × {{ $product->quantity_purchase }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted">${{ number_format($order->total_shipping, 2) }}</td>
                        <td>
                            @if($order->shipping_address_json)
                                {{ $order->shipping_address_json['city'] }}, {{ $order->shipping_address_json['country'] }}
                            @else
                                Local Pickup
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top text-center py-3">
            <a href="#" class="text-decoration-none small text-dark fw-bold">Learn more about orders</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#btn-sync-orders').on('click', function() {
        let btn = $(this);
        // 添加加载动画效果
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Retrieving...');

        $.ajax({
            url: "{{ url('/marketplace/orders/sync') }}", // 假设这是你的后端同步路由
            method: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // alert('Orders updated successfully!');
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise me-1"></i> Retrieve Latest Order');
            }
        });
    });

    $(document).ready(function() {
        $('input[name="date_filter"]').on('change', function() {
            let range = 7; // default
            let value = $(this).val();

            if (value === 'today') range = 0;
            if (value === 'last_7') range = 7;
            if (value === 'last_30') range = 30;

            // Redirect the page with the new range parameter
            // This will trigger the index() function again with the new value
            window.location.href = "{{ url('/marketplace-order') }}?range=" + range;
        });
    });
</script>
@endpush
