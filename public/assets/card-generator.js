(function () {
    const root = document.querySelector('[data-card-generator]');
    if (!root) return;

    const payload = JSON.parse(root.querySelector('[data-cardholder-json]').textContent);
    const frontCanvas = document.getElementById('front-card');
    const backCanvas = document.getElementById('back-card');
    const frontDownload = document.querySelector('[data-download-front]');
    const backDownload = document.querySelector('[data-download-back]');

    const CARD_W = 1011;
    const CARD_H = 638;

    function setupCanvas(canvas) {
        canvas.width = CARD_W;
        canvas.height = CARD_H;
    }

    function slugify(value) {
        return String(value || 'senior-citizen-card')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function loadImage(url) {
        return new Promise((resolve) => {
            if (!url) {
                resolve(null);
                return;
            }

            const img = new Image();

            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);

            img.src = url;
        });
    }

    function loadPhoto(url) {
        return new Promise((resolve) => {
            if (!url) {
                resolve(null);
                return;
            }

            const img = new Image();

            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);

            img.src = url;
        });
    }

    function wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 3) {
        const words = String(text || '').split(/\s+/).filter(Boolean);
        let line = '';
        let lines = [];

        for (const word of words) {
            const testLine = line ? line + ' ' + word : word;

            if (ctx.measureText(testLine).width > maxWidth && line) {
                lines.push(line);
                line = word;
            } else {
                line = testLine;
            }
        }

        if (line) {
            lines.push(line);
        }

        lines = lines.slice(0, maxLines);

        for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], x, y + i * lineHeight);
        }
    }

    function drawCroppedImage(ctx, img, x, y, w, h) {
        const sourceAspect = img.width / img.height;
        const targetAspect = w / h;

        let sx = 0;
        let sy = 0;
        let sw = img.width;
        let sh = img.height;

        if (sourceAspect > targetAspect) {
            sw = img.height * targetAspect;
            sx = (img.width - sw) / 2;
        } else {
            sh = img.width / targetAspect;
            sy = (img.height - sh) / 2;
        }

        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
    }

    function drawWhitePhotoPlaceholder(ctx, x, y, w, h) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x, y, w, h);
    }

    function fitText(ctx, text, x, y, maxWidth, startingFontSize, minFontSize, fontFamily, fontWeight = 'bold') {
        let fontSize = startingFontSize;
        const value = String(text || '');

        do {
            ctx.font = `${fontWeight} ${fontSize}px ${fontFamily}`;

            if (ctx.measureText(value).width <= maxWidth) {
                break;
            }

            fontSize -= 1;
        } while (fontSize >= minFontSize);

        ctx.fillText(value, x, y);
    }

    function getTemplatePath(side) {
        const cardTypeSlug = slugify(
            payload.card_type.slug ||
            payload.card_type.name ||
            'senior-citizen-card'
        );

        return `/assets/card-templates/${cardTypeSlug}/${side}.png`;
    }

    async function drawFront() {
        setupCanvas(frontCanvas);
    
        const ctx = frontCanvas.getContext('2d');
        ctx.clearRect(0, 0, CARD_W, CARD_H);
    
        const template = await loadImage(getTemplatePath('front'));
    
        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, CARD_W, CARD_H);
    
            ctx.fillStyle = '#DE6900';
            ctx.font = 'bold 28px Arial';
            ctx.fillText('Missing front template PNG', 40, 70);
        }
    
        /*
            PHOTO
            These match your front layout fairly closely.
            Adjust only if the photo is slightly off.
        */
        const photoX = 57;
        const photoY = 222;
        const photoW = 312;
        const photoH = 312;
    
        const photo = await loadPhoto(payload.photo_url);
    
        if (photo) {
            drawCroppedImage(ctx, photo, photoX, photoY, photoW, photoH);
        } else {
            drawWhitePhotoPlaceholder(ctx, photoX, photoY, photoW, photoH);
        }
    
        ctx.fillStyle = '#000000';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'left';
    
        function fitLeftText(text, x, y, maxWidth, startingFontSize, minFontSize = 18) {
            let fontSize = startingFontSize;
            const value = String(text || '');
    
            do {
                ctx.font = `bold ${fontSize}px Arial`;
    
                if (ctx.measureText(value).width <= maxWidth) {
                    break;
                }
    
                fontSize -= 1;
            } while (fontSize >= minFontSize);
    
            ctx.fillText(value, x, y);
        }
    
        /*
            IMPORTANT:
            The template already contains the labels.
            So below, we draw ONLY the values.
        */
    
        // ID NO value
        fitLeftText(
            payload.id_no || '',
            560,
            150,
            230,
            28,
            18
        );
    
        // NAME value
        fitLeftText(
            String(payload.name || '').toUpperCase(),
            520,
            210,
            400,
            26,
            16
        );
    
        // SC ID value
        fitLeftText(
            payload.sc_id || '',
            560,
            271,
            300,
            26,
            16
        );
    
        // PHILHEALTH value
        fitLeftText(
            payload.philhealth || '',
            630,
            331,
            250,
            26,
            16
        );
    
        // CELLPHONE NO value
        fitLeftText(
            payload.cellphone_no || '',
            650,
            390,
            250,
            24,
            16
        );
    
        // ADDRESS value
        ctx.font = 'bold 22px Arial';
        wrapText(
            ctx,
            String(payload.address || '').toUpperCase(),
            438,
            465,
            500,
            30,
            2
        );
    
        // POSITION value
        fitLeftText(
            String(payload.position || '').toUpperCase(),
            620,
            555,
            240,
            24,
            16
        );
    
        // Reset
        ctx.textAlign = 'left';
        ctx.textBaseline = 'alphabetic';
    }

    async function drawBack() {
        setupCanvas(backCanvas);
    
        const ctx = backCanvas.getContext('2d');
        ctx.clearRect(0, 0, CARD_W, CARD_H);
    
        const template = await loadImage(getTemplatePath('back'));
    
        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, CARD_W, CARD_H);
    
            ctx.fillStyle = '#DE6900';
            ctx.font = 'bold 28px Arial';
            ctx.fillText('Missing back template PNG', 40, 70);
        }
    
        ctx.fillStyle = '#000000';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';
    
        function fitCenteredText(text, centerX, centerY, maxWidth, startingFontSize, minFontSize = 18) {
            let fontSize = startingFontSize;
            const value = String(text || '');
    
            do {
                ctx.font = `bold ${fontSize}px Arial`;
    
                if (ctx.measureText(value).width <= maxWidth) {
                    break;
                }
    
                fontSize -= 1;
            } while (fontSize >= minFontSize);
    
            ctx.fillText(value, centerX, centerY);
        }
    
        /*
            BACK TEMPLATE COORDINATES
            These match the correct back-card layout you sent.
            The template already contains the labels.
            The script only draws the values.
        */
    
        // Birthday value — left white box
        fitCenteredText(
            payload.birthday || '',
            275,
            195,
            390,
            28,
            18
        );
    
        // Age value — right white box
        fitCenteredText(
            payload.age || '',
            735,
            195,
            390,
            28,
            18
        );
    
        // Emergency contact person — middle white box
        fitCenteredText(
            String(payload.contact_name || '').toUpperCase(),
            506,
            319,
            500,
            28,
            18
        );
    
        // Emergency contact number — lower white box
        fitCenteredText(
            payload.emergency_contact_number || '',
            506,
            450,
            500,
            28,
            18
        );
    
        // ID number — bottom-right corner
        ctx.textAlign = 'right';
        ctx.textBaseline = 'alphabetic';
        ctx.font = 'bold 28px Arial';
        ctx.fillText(
            payload.id_no || '',
            870,
            610
        );
    
        // Reset alignment so other functions are not affected
        ctx.textAlign = 'left';
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

    frontDownload?.addEventListener('click', () => {
        downloadCanvas(frontCanvas, `${payload.id_no}_FRONT.png`);
    });

    backDownload?.addEventListener('click', () => {
        downloadCanvas(backCanvas, `${payload.id_no}_BACK.png`);
    });

    drawFront();
    drawBack();
})();