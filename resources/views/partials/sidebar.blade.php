<div class="sidebar">
    <div class="logo-section">
        <div class="logo-circle">{{ strtoupper(substr($customer->customers_name, 0, 1)) }}</div>
        <span class="fw-bold fs-4">{{ $customer->customers_name }}</span>
    </div>

    <nav class="nav flex-column">
        {{-- The request()->is('path*') check adds 'active' class if the URL matches --}}
        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">
            <i class="bi bi-house-door"></i> Home
        </a>
        <a class="nav-link {{ request()->is('marketplace-customer*') ? 'active' : '' }}" href="/marketplace-customer">
            <i class="bi bi-people"></i> Customers
        </a>
        <a class="nav-link {{ request()->is('marketplace-order*') ? 'active' : '' }}" href="/marketplace-order">
            <i class="bi bi-card-list"></i> Orders
        </a>
        <a class="nav-link {{ request()->is('marketplace-merchant*') ? 'active' : '' }}" href="/marketplace-merchant">
            <i class="bi bi-shop"></i> Marketplace
        </a>
        <a class="nav-link {{ request()->is('marketplace-report*') ? 'active' : '' }}" href="/marketplace-report">
            <i class="bi bi-graph-up"></i> Report
        </a>
        <a class="nav-link {{ request()->is('marketplace-setting*') ? 'active' : '' }}" href="/marketplace-setting">
            <i class="bi bi-gear"></i> Setting
        </a>
    </nav>

    <form action="/logout" method="POST" class="mt-5 pt-5">
        @csrf
        <button type="submit" class="nav-link border-0 bg-transparent w-100 text-danger text-start">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </button>
    </form>
</div>