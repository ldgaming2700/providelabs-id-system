(function () {
    const root = document.querySelector('[data-card-generator]');
    if (!root) return;

    const payload = JSON.parse(root.querySelector('[data-cardholder-json]').textContent);
    const frontCanvas = document.getElementById('front-card');
    const backCanvas = document.getElementById('back-card');
    const frontDownload = document.querySelector('[data-download-front]');
    const backDownload = document.querySelector('[data-download-back]');
    const CARD_W = 1011, CARD_H = 638;

    function setupCanvas(canvas) { canvas.width = CARD_W; canvas.height = CARD_H; }

    function wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 3) {
        const words = String(text || '').split(/\s+/).filter(Boolean);
        let line = '', lines = [];
        for (const word of words) {
            const testLine = line ? line + ' ' + word : word;
            if (ctx.measureText(testLine).width > maxWidth && line) {
                lines.push(line); line = word;
            } else line = testLine;
        }
        if (line) lines.push(line);
        lines = lines.slice(0, maxLines);
        for (let i = 0; i < lines.length; i++) ctx.fillText(lines[i], x, y + i * lineHeight);
    }

    function roundedRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
    }

    function drawLabelValue(ctx, label, value, x, y, w) {
        ctx.fillStyle = '#64748b';
        ctx.font = 'bold 21px Arial';
        ctx.fillText(label, x, y);
        ctx.fillStyle = '#111827';
        ctx.font = 'bold 28px Arial';
        wrapText(ctx, value || '-', x, y + 34, w, 32, 2);
    }

    function loadPhoto(url) {
        return new Promise((resolve) => {
            if (!url) return resolve(null);
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = url;
        });
    }

    function drawCroppedImage(ctx, img, x, y, w, h) {
        const sourceAspect = img.width / img.height;
        const targetAspect = w / h;
        let sx = 0, sy = 0, sw = img.width, sh = img.height;
        if (sourceAspect > targetAspect) {
            sw = img.height * targetAspect;
            sx = (img.width - sw) / 2;
        } else {
            sh = img.width / targetAspect;
            sy = (img.height - sh) / 2;
        }
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
    }

    async function drawFront() {
        setupCanvas(frontCanvas);
        const ctx = frontCanvas.getContext('2d');
        const primary = payload.card_type.primary_color || '#DE6900';
        const secondary = payload.card_type.secondary_color || '#63C7D1';
        ctx.clearRect(0, 0, CARD_W, CARD_H);
        ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, CARD_W, CARD_H);
        ctx.fillStyle = secondary; ctx.fillRect(0, 0, CARD_W, 104);
        ctx.fillStyle = primary; ctx.fillRect(0, 104, CARD_W, 28);
        ctx.fillStyle = '#ffffff'; ctx.font = 'bold 42px Arial'; ctx.fillText('PROVIDELABS CORPORATION', 52, 67);
        ctx.font = 'bold 26px Arial'; ctx.fillText(payload.card_type.front_title || payload.card_type.name, 665, 68);

        ctx.fillStyle = '#f8fafc'; roundedRect(ctx, 52, 174, 330, 330, 20); ctx.fill();
        ctx.strokeStyle = primary; ctx.lineWidth = 8; roundedRect(ctx, 52, 174, 330, 330, 20); ctx.stroke();

        const photo = await loadPhoto(payload.photo_url);
        if (photo) {
            ctx.save(); roundedRect(ctx, 62, 184, 310, 310, 16); ctx.clip(); drawCroppedImage(ctx, photo, 62, 184, 310, 310); ctx.restore();
        } else {
            ctx.fillStyle = '#ffffff'; roundedRect(ctx, 62, 184, 310, 310, 16); ctx.fill();
            ctx.fillStyle = '#94a3b8'; ctx.font = 'bold 28px Arial'; ctx.textAlign = 'center'; ctx.fillText('NO PHOTO', 217, 347); ctx.textAlign = 'left';
        }

        ctx.fillStyle = '#111827'; ctx.font = 'bold 48px Arial'; wrapText(ctx, payload.name, 420, 205, 540, 52, 2);
        drawLabelValue(ctx, 'ID NO', payload.id_no, 420, 328, 220);
        drawLabelValue(ctx, 'SC ID / REF NO', payload.sc_id, 650, 328, 300);
        drawLabelValue(ctx, 'PHILHEALTH', payload.philhealth, 420, 422, 300);
        drawLabelValue(ctx, 'CELLPHONE NO.', payload.cellphone_no, 650, 422, 300);

        ctx.fillStyle = '#ffffff'; roundedRect(ctx, 52, 526, 907, 82, 18); ctx.fill();
        ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 3; roundedRect(ctx, 52, 526, 907, 82, 18); ctx.stroke();
        ctx.fillStyle = primary; ctx.font = 'bold 20px Arial'; ctx.fillText('ADDRESS', 76, 556);
        ctx.fillStyle = '#111827'; ctx.font = 'bold 25px Arial'; wrapText(ctx, payload.address, 76, 588, 850, 28, 2);
        ctx.fillStyle = primary; ctx.fillRect(0, CARD_H - 16, CARD_W, 16);
    }

    function drawBack() {
        setupCanvas(backCanvas);
        const ctx = backCanvas.getContext('2d');
        const primary = payload.card_type.primary_color || '#DE6900';
        const secondary = payload.card_type.secondary_color || '#63C7D1';
        ctx.clearRect(0, 0, CARD_W, CARD_H);
        ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, CARD_W, CARD_H);
        ctx.fillStyle = primary; ctx.fillRect(0, 0, CARD_W, 110);
        ctx.fillStyle = secondary; ctx.fillRect(0, 110, CARD_W, 24);
        ctx.fillStyle = '#ffffff'; ctx.font = 'bold 42px Arial'; ctx.fillText(payload.card_type.back_title || 'CARDHOLDER INFORMATION', 52, 70);
        ctx.fillStyle = '#f8fafc'; roundedRect(ctx, 52, 174, 907, 340, 22); ctx.fill();
        ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 4; roundedRect(ctx, 52, 174, 907, 340, 22); ctx.stroke();
        drawLabelValue(ctx, 'ID NO', payload.id_no, 90, 225, 360);
        drawLabelValue(ctx, 'BIRTHDAY', payload.birthday || '-', 90, 330, 360);
        drawLabelValue(ctx, 'AGE', payload.age ? String(payload.age) : '-', 520, 330, 160);
        drawLabelValue(ctx, 'CONTACT PERSON', payload.contact_name, 90, 435, 400);
        drawLabelValue(ctx, 'EMERGENCY CONTACT NUMBER', payload.emergency_contact_number, 520, 435, 400);
        ctx.fillStyle = '#475569'; ctx.font = '22px Arial'; ctx.fillText('This card is issued for identification and community assistance purposes.', 90, 562);
        ctx.fillStyle = primary; ctx.fillRect(0, CARD_H - 16, CARD_W, 16);
    }

    function downloadCanvas(canvas, filename) {
        canvas.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();
            URL.revokeObjectURL(url);
        }, 'image/png');
    }

    frontDownload?.addEventListener('click', () => downloadCanvas(frontCanvas, `${payload.id_no}_FRONT.png`));
    backDownload?.addEventListener('click', () => downloadCanvas(backCanvas, `${payload.id_no}_BACK.png`));
    drawFront(); drawBack();
})();
