document.addEventListener("DOMContentLoaded", () => {
    const pusher = new Pusher('368533d9bcc67e881fff', {
        cluster: 'ap1'
    });

    const channel = pusher.subscribe('notification-channel');

    // Request permission on user interaction
    let notificationPermissionGranted = false;
    const requestNotificationPermission = () => {
        if (!("Notification" in window)) {
            alert("This browser does not support notifications.");
            return;
        }

        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                notificationPermissionGranted = true;
                console.log("Notification permission granted.");
            } else if (permission === "denied") {
                console.log("Notification permission denied.");
            }
        });
    };

    // Bind to Pusher event
    channel.bind('notification-send', function (data) {
        console.log(data);

        if (notificationPermissionGranted) {
            showNotification(data);
        } else {
            console.log("Notification permission not granted. Ignoring notification.", data);
        }
    });

    function showNotification(data) {
        const notification = new Notification("New Information", {
            body: data.message,
            icon: "https://via.placeholder.com/80",
        });

        notification.onclick = () => {
            window.location.href = data.url;
        };
    }

    // Add an interaction event to request notification permission
    document.body.addEventListener("click", requestNotificationPermission, { once: true });
});
