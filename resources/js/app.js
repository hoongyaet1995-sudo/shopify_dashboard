import './bootstrap';

$(document).ready(function() {
    // Create overlay element if it doesn't exist
    if ($('.sidebar-overlay').length === 0) {
        $('body').append('<div class="sidebar-overlay"></div>');
    }

    // Toggle Sidebar
    $('#sidebarToggle, .sidebar-overlay').on('click', function() {
        $('.sidebar').toggleClass('show');
        $('.sidebar-overlay').toggleClass('active');
    });
});