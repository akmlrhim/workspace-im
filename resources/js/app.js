import Sortable from "sortablejs";
import "./echo";
import "./uploads";

window.Sortable = Sortable;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}
