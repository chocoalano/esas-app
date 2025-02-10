import * as faceapi from 'face-api.js';

document.addEventListener("DOMContentLoaded", async () => {
    const video = document.getElementById('video');
    const statusText = document.getElementById('status');
    const canvasElement = document.getElementById('canvas');
    const context = canvasElement.getContext('2d');
    let labeledFaceDescriptors = null;

    async function loadModels() {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
            faceapi.nets.faceExpressionNet.loadFromUri('/models'),
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models') // More accurate for liveness
        ]);
        console.log("✅ Face API models loaded!");
    }

    async function startVideo() {
        try {
            if (navigator.permissions) {
                const permissions = await navigator.permissions.query({ name: "camera" }).catch(() => null);
                if (permissions && permissions.state === "denied") {
                    statusText.innerText = "❌ Akses kamera ditolak!";
                    return;
                }
            }

            const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
            video.srcObject = stream;

            video.onloadedmetadata = () => {
                video.play();
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
                faceapi.matchDimensions(canvasElement, { width: video.videoWidth, height: video.videoHeight });
                detectFace();
            };
        } catch (error) {
            console.error("❌ Gagal mengakses kamera:", error);
            statusText.innerText = "❌ Akses kamera ditolak!";
        }
    }

    async function loadLabeledImages() {
        var nip = document.getElementById('nip').value;
        const labels = [nip];
        const faceDescriptors = await Promise.all(
            labels.map(async (label) => {
                try {
                    const img = await faceapi.fetchImage(`/api/assets/avatar-users/${label}.png`);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                    if (!detection) {
                        console.warn(`❌ Wajah tidak ditemukan di gambar ${label}`);
                        return null;
                    }
                    return new faceapi.LabeledFaceDescriptors(label, [detection.descriptor]);
                } catch (error) {
                    console.error(`❌ Error memuat gambar ${label}:`, error);
                    return null;
                }
            })
        );

        return faceDescriptors.filter(Boolean); // Remove `null` results
    }

    async function detectFace() {
        setInterval(async () => {
            try {
                const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
                    .withFaceLandmarks()
                    .withFaceExpressions()
                    .withFaceDescriptors();

                context.clearRect(0, 0, canvasElement.width, canvasElement.height);

                if (detections.length > 0) {
                    const resizedDetections = faceapi.resizeResults(detections, { width: video.videoWidth, height: video.videoHeight });

                    faceapi.draw.drawDetections(canvasElement, resizedDetections);
                    faceapi.draw.drawFaceLandmarks(canvasElement, resizedDetections);
                    faceapi.draw.drawFaceExpressions(canvasElement, resizedDetections);

                    const expressions = detections[0].expressions;
                    const isLiveness = expressions.happy > 0.5 || expressions.surprised > 0.5; // Higher sensitivity for liveness
                    statusText.innerText = isLiveness ? "✅ Liveness: OK" : "❌ Liveness: Gagal";

                    if (labeledFaceDescriptors) {
                        const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);
                        const bestMatch = faceMatcher.findBestMatch(detections[0].descriptor);
                        statusText.innerText += ` | Pengenalan: ${bestMatch.toString()}`;

                        // Check if liveness is true and bestMatch distance is above the threshold (0.45)
                        if (isLiveness && bestMatch.distance > 0.40) {
                            // Prepare data to be sent
                            var nip = document.getElementById('nip').value;
                            var departement = document.getElementById('departement-selected').value;
                            var timework = document.getElementById('timework-selected').value;
                            var type = document.getElementById('type-selected').value;
                            const postData = {
                                nip: nip,
                                departement: departement,
                                timework: timework,
                                type: type,
                            };
                            // console.log(postData);

                            // Send POST request to the Laravel backend
                            try {
                                const csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;
                                const response = await fetch('/face-recognition', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    body: JSON.stringify(postData),
                                });
                                const responseData = await response.json().catch(() => null);
                                if (response.ok) {
                                    document.getElementById("myForm").reset();
                                    window.location.href = "/face-recognition";
                                } else {
                                    const errorMessage = responseData?.data?.error || responseData?.message || response.statusText;
                                    console.error("Failed to post data:", errorMessage);
                                    alert(`Gagal mengirim data! (${response.status}): ${errorMessage}`);
                                }
                            } catch (error) {
                                console.error("Error posting data:", error);
                            }
                        }
                    }
                } else {
                    statusText.innerText = "🔍 Menunggu wajah...";
                }
            } catch (error) {
                alert("❌ Error saat mendeteksi wajah:", error);
            }
        }, 500);
    }

    async function init() {
        await loadModels();
        labeledFaceDescriptors = await loadLabeledImages();
        if (labeledFaceDescriptors.length === 0) {
            alert("⚠️ Tidak ada wajah yang terdaftar, pastikan anda sudah memperbaharui foto profil anda pada aplikasi esas!");
            console.warn("⚠️ Tidak ada wajah yang terdaftar!");
        }
        await startVideo();
    }

    init();
});
