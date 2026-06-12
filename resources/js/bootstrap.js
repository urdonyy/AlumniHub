import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

/**
 * Laravel Echo + Pusher (real-time messaging).
 *
 * Guarded: only initializes when a Pusher key is present at build time. Without
 * keys (local dev / WS down) the app silently falls back to AJAX polling, and
 * nothing here throws. window.Echo stays undefined, which callers check for.
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

if (pusherKey) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'ap1',
        forceTLS: true,
        // Echo sends the socket id on this header so broadcast()->toOthers()
        // can exclude the sender's own connection.
        auth: {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        },
    });
}
