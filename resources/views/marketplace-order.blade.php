@extends('layouts.app')

@section('title', 'Order History - ' . $customer->customers_name)

@section('content')
<div class="container-fluid">
    <h2 class="fw-bold mb-4">Marketplace Order</h2>

    <div class="alert alert-light border-start border-primary border-4 shadow-sm mb-4" role="alert">
        <span class="text-muted">
            Order History 
            <span class="badge bg-light text-dark border">This has to be approved by your service provider.</span>
        </span>
    </div>

    <div class="dashboard-card">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>Merchant Name</th>
                    <th class="text-center">Shop Name</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($merchants as $merchant)
                <tr>
                    <td>{{ $merchant->marketplace_user_name }}</td>
                    <td class="text-center">
                        <span class="badge bg-dark">{{ $merchant->marketplace_shop_name . '.myshopify.com' }}</span>
                    </td>
                    <td class="text-end">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            onclick="authorizeStore({{ $merchant->marketplace_user_id }})">
                            Re-Authorize Now
                        </button>
                    </td>
                </tr>
                @endforeach

                @if($merchants->isEmpty())
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <p class="text-muted">No orders or merchants connected yet.</p>
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

{{-- Keep the modal here if you want it accessible from this page --}}
<div class="modal fade" id="connectStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Connect New Store</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-body-content" class="modal-body">
                {{-- Content loaded via AJAX --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // If you need the same AJAX logic as the merchant page, 
    // it's better to put that script in a separate file or 
    // paste the authorizeStore function here.
    function authorizeStore(storeId) {
        $.ajax({
            url: "{{ url('/merchants/authorize') }}",
            method: "POST",
            data: {
                store_id: storeId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    location.reload();
                }
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'Something went wrong');
            }
        });
    }
</script>
@endpush