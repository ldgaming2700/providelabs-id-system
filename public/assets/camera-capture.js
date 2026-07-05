(function () {
    const root = document.querySelector('[data-camera-root]');
    if (!root) return;

    const video = root.querySelector('[data-camera-video]');
    const canvas = root.querySelector('[data-camera-canvas]');
    const preview = root.querySelector('[data-camera-preview]');
    const hiddenInput = document.querySelector('[name="captured_photo"]');
    const startButton = root.querySelector('[data-camera-start]');
    const captureButton = root.querySelector('[data-camera-capture]');
    const stopButton = root.querySelector('[data-camera-stop]');
    let stream = null;

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Camera access is not available in this browser. Use photo upload instead.');
            return;
        }
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 1280 } },
            audio: false
        });
        video.srcObject = stream;
        video.hidden = false;
        captureButton.disabled = false;
        stopButton.disabled = false;
    }

    function stopCamera() {
        if (stream) stream.getTracks().forEach(track => track.stop());
        stream = null;
        video.srcObject = null;
        video.hidden = true;
        captureButton.disabled = true;
        stopButton.disabled = true;
    }

    function capturePhoto() {
        const sourceWidth = video.videoWidth;
        const sourceHeight = video.videoHeight;
        const size = Math.min(sourceWidth, sourceHeight);
        const sx = (sourceWidth - size) / 2;
        const sy = (sourceHeight - size) / 2;
        canvas.width = 900;
        canvas.height = 900;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, sx, sy, size, size, 0, 0, 900, 900);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
        hiddenInput.value = dataUrl;
        preview.src = dataUrl;
        preview.hidden = false;
    }

    startButton?.addEventListener('click', async () => {
        try { await startCamera(); } catch (error) { alert('Unable to access camera: ' + error.message); }
    });
    captureButton?.addEventListener('click', capturePhoto);
    stopButton?.addEventListener('click', stopCamera);
    window.addEventListener('beforeunload', stopCamera);
})();
