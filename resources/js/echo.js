import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: document.querySelector('meta[name="pusher-key"]')?.content,
    cluster:
        document.querySelector('meta[name="pusher-cluster"]')?.content ?? "mt1",
    wsHost: import.meta.env.VITE_PUSHER_HOST
        ? import.meta.env.VITE_PUSHER_HOST
        : `ws-${document.querySelector('meta[name="pusher-cluster"]')?.content ?? "mt1"}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: true,
    enabledTransports: ["ws", "wss"],
});
