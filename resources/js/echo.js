import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;

const cluster = meta("pusher-cluster") ?? "mt1";

window.Echo = new Echo({
    broadcaster: "pusher",
    key: meta("pusher-key"),
    cluster,
    wsHost: `ws-${cluster}.pusher.com`,
    wsPort: 80,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ["ws", "wss"],
});

// Log connection errors in development so WebSocket issues are visible in console
window.Echo.connector.pusher.connection.bind("error", (err) => {
    if (import.meta.env.DEV) {
        console.warn("[Echo] Pusher connection error:", err);
    }
});

window.Echo.connector.pusher.connection.bind("unavailable", () => {
    if (import.meta.env.DEV) {
        console.warn("[Echo] Pusher unavailable — real-time updates paused.");
    }
});
