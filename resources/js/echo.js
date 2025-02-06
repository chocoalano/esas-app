import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: import.meta.env.BROADCAST_CONNECTION || 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

document.addEventListener("DOMContentLoaded", () => {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY || '368533d9bcc67e881fff';
    const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1';

    const pusher = new Pusher(pusherKey, { cluster: pusherCluster });
    const channel = pusher.subscribe('notification-channel');

    let notificationPermissionGranted = false;

    const requestNotificationPermission = () => {
        if (!("Notification" in window)) {
            alert("Browser tidak mendukung notifikasi.");
            return;
        }

        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                notificationPermissionGranted = true;
                console.log("Izin notifikasi diberikan.");
            } else {
                console.log("Izin notifikasi ditolak.");
            }
        });
    };

    channel.bind('notification-send', (data) => {
        console.log("Pesan diterima:", data);

        if (notificationPermissionGranted) {
            showNotification(data);
        } else {
            console.warn("Izin notifikasi belum diberikan.", data);
        }
    });

    const showNotification = (data) => {
        const notification = new Notification("New Information", {
            body: data.message,
            icon: "https://via.placeholder.com/80",
        });

        notification.onclick = () => {
            window.location.href = data.url;
        };
    };

    document.body.addEventListener("click", requestNotificationPermission, { once: true });
});
