@auth
    <script
        src="{{ asset('assets/js/rms-realtime.js') }}"
        data-tenant-id="{{ auth()->user()->tenant_id }}"
        data-channels="{{ $channels ?? 'orders' }}"
        defer
    ></script>
@endauth
