import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.MIX_REVERB_APP_KEY || process.env.VITE_REVERB_APP_KEY,
    wsHost: process.env.MIX_REVERB_HOST || process.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: process.env.MIX_REVERB_PORT || process.env.VITE_REVERB_PORT || 8080,
    wssPort: process.env.MIX_REVERB_PORT || process.env.VITE_REVERB_PORT || 8080,
    forceTLS: (process.env.MIX_REVERB_SCHEME || process.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
