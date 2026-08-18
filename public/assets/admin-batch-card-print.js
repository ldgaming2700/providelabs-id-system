(function () {
    const CARD_W = 1011;
    const CARD_H = 638;

    const payloadElement =
        document.getElementById('batch-cardholder-json');

    const printArea =
        document.getElementById('print-area');

    const printButton =
        document.getElementById('print-button');

    const notice =
        document.getElementById('render-notice');

    if (!payloadElement || !printArea || !printButton) {
        return;
    }

    let payload;

    try {
        payload = JSON.parse(payloadElement.textContent);
    } catch (error) {
        console.error('Could not parse batch cardholder JSON.', error);
        return;
    }

    const side =
        printArea.dataset.side === 'back'
            ? 'back'
            : 'front';

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function getCardTypeSlug(cardholder) {
        if (cardholder.card_type?.slug) {
            return slugify(cardholder.card_type.slug);
        }

        if (cardholder.card_type?.name) {
            return slugify(cardholder.card_type.name);
        }

        return 'senior-citizen-card';
    }

    function getTemplatePath(cardholder, requestedSide) {
        return (
            '/assets/card-templates/'
            + getCardTypeSlug(cardholder)
            + '/'
            + requestedSide
            + '.png'
        );
    }

    function loadImage(url) {
        return new Promise((resolve) => {
            if (!url) {
                resolve(null);
                return;
            }

            const image = new Image();

            image.onload = function () {
                resolve(image);
            };

            image.onerror = function () {
                console.error('Unable to load image:', url);
                resolve(null);
            };

            image.src = url;
        });
    }

    function loadPhoto(url) {
        return new Promise((resolve) => {
            if (!url) {
                resolve(null);
                return;
            }

            const image = new Image();

            image.onload = function () {
                resolve(image);
            };

            image.onerror = function () {
                console.error('Unable to load cardholder photo:', url);
                resolve(null);
            };

            image.src = url;
        });
    }

    function drawMissingTemplate(ctx, cardholder, requestedSide) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, CARD_W, CARD_H);

        ctx.fillStyle = '#b91c1c';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = 'bold 26px Arial';

        ctx.fillText(
            'Missing '
                + requestedSide
                + ' template: '
                + getCardTypeSlug(cardholder),
            CARD_W / 2,
            CARD_H / 2
        );

        ctx.textAlign = 'left';
        ctx.textBaseline = 'alphabetic';
    }

    function drawCroppedImage(ctx, image, x, y, width, height) {
        const sourceAspect = image.width / image.height;
        const targetAspect = width / height;

        let sourceX = 0;
        let sourceY = 0;
        let sourceWidth = image.width;
        let sourceHeight = image.height;

        if (sourceAspect > targetAspect) {
            sourceWidth = image.height * targetAspect;
            sourceX = (image.width - sourceWidth) / 2;
        } else {
            sourceHeight = image.width / targetAspect;
            sourceY = (image.height - sourceHeight) / 2;
        }

        ctx.drawImage(
            image,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            x,
            y,
            width,
            height
        );
    }

    function drawCircularCroppedImage(
        ctx,
        image,
        centerX,
        centerY,
        radius
    ) {
        ctx.save();

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.closePath();
        ctx.clip();

        drawCroppedImage(
            ctx,
            image,
            centerX - radius,
            centerY - radius,
            radius * 2,
            radius * 2
        );

        ctx.restore();
    }

    function drawWhiteRectanglePlaceholder(
        ctx,
        x,
        y,
        width,
        height
    ) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x, y, width, height);
    }

    function drawWhiteCirclePlaceholder(
        ctx,
        centerX,
        centerY,
        radius
    ) {
        ctx.save();

        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    function fitLeftText(
        ctx,
        text,
        x,
        y,
        maxWidth,
        startingFontSize,
        minimumFontSize,
        fontFamily,
        fontWeight
    ) {
        let fontSize = startingFontSize;
        const value = String(text || '');

        while (fontSize >= minimumFontSize) {
            ctx.font =
                (fontWeight || 'bold')
                + ' '
                + fontSize
                + 'px '
                + (fontFamily || 'Arial');

            if (ctx.measureText(value).width <= maxWidth) {
                break;
            }

            fontSize -= 1;
        }

        ctx.fillText(value, x, y);
    }

    function fitCenteredText(
        ctx,
        text,
        centerX,
        centerY,
        maxWidth,
        startingFontSize,
        minimumFontSize,
        fontFamily,
        fontWeight
    ) {
        let fontSize = startingFontSize;
        const value = String(text || '');

        while (fontSize >= minimumFontSize) {
            ctx.font =
                (fontWeight || 'bold')
                + ' '
                + fontSize
                + 'px '
                + (fontFamily || 'Arial');

            if (ctx.measureText(value).width <= maxWidth) {
                break;
            }

            fontSize -= 1;
        }

        const oldAlign = ctx.textAlign;
        const oldBaseline = ctx.textBaseline;

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(value, centerX, centerY);

        ctx.textAlign = oldAlign;
        ctx.textBaseline = oldBaseline;
    }

    function buildWrappedLines(ctx, text, maxWidth, maximumLines) {
        const words = String(text || '')
            .split(/\s+/)
            .filter(Boolean);

        const lines = [];
        let currentLine = '';

        for (const word of words) {
            const testLine = currentLine
                ? currentLine + ' ' + word
                : word;

            if (
                ctx.measureText(testLine).width > maxWidth
                && currentLine
            ) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        }

        if (currentLine) {
            lines.push(currentLine);
        }

        if (lines.length > maximumLines) {
            const limited = lines.slice(0, maximumLines);
            let finalLine = limited[maximumLines - 1];

            while (
                finalLine.length > 0
                && ctx.measureText(finalLine + '...').width > maxWidth
            ) {
                finalLine = finalLine.slice(0, -1);
            }

            limited[maximumLines - 1] = finalLine.trim() + '...';

            return limited;
        }

        return lines;
    }

    function drawLeftWrappedText(
        ctx,
        text,
        x,
        firstBaselineY,
        maxWidth,
        lineHeight,
        maximumLines
    ) {
        const lines = buildWrappedLines(
            ctx,
            text,
            maxWidth,
            maximumLines
        );

        lines.forEach(function (line, index) {
            ctx.fillText(
                line,
                x,
                firstBaselineY + index * lineHeight
            );
        });
    }

    function drawCenteredWrappedText(
        ctx,
        text,
        centerX,
        centerY,
        maxWidth,
        lineHeight,
        maximumLines
    ) {
        const lines = buildWrappedLines(
            ctx,
            text,
            maxWidth,
            maximumLines
        );

        if (!lines.length) {
            return;
        }

        const oldAlign = ctx.textAlign;
        const oldBaseline = ctx.textBaseline;

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const totalHeight = (lines.length - 1) * lineHeight;
        const firstCenterY = centerY - totalHeight / 2;

        lines.forEach(function (line, index) {
            ctx.fillText(
                line,
                centerX,
                firstCenterY + index * lineHeight
            );
        });

        ctx.textAlign = oldAlign;
        ctx.textBaseline = oldBaseline;
    }

    async function drawSeniorCitizenFront(canvas, cardholder) {
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath(cardholder, 'front')
        );

        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            drawMissingTemplate(ctx, cardholder, 'front');
        }

        const photo = await loadPhoto(cardholder.photo_url);

        const photoX = 57;
        const photoY = 222;
        const photoWidth = 312;
        const photoHeight = 312;

        if (photo) {
            drawCroppedImage(
                ctx,
                photo,
                photoX,
                photoY,
                photoWidth,
                photoHeight
            );
        } else {
            drawWhiteRectanglePlaceholder(
                ctx,
                photoX,
                photoY,
                photoWidth,
                photoHeight
            );
        }

        ctx.fillStyle = '#000000';
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';

        fitLeftText(
            ctx,
            cardholder.id_no,
            560,
            150,
            230,
            28,
            18,
            'Arial',
            'bold'
        );

        fitLeftText(
            ctx,
            String(cardholder.name || '').toUpperCase(),
            520,
            210,
            400,
            26,
            16,
            'Arial',
            'bold'
        );

        fitLeftText(
            ctx,
            cardholder.sc_id,
            560,
            271,
            300,
            26,
            16,
            'Arial',
            'bold'
        );

        fitLeftText(
            ctx,
            cardholder.philhealth,
            630,
            331,
            250,
            26,
            16,
            'Arial',
            'bold'
        );

        fitLeftText(
            ctx,
            cardholder.cellphone_no,
            650,
            390,
            250,
            24,
            16,
            'Arial',
            'bold'
        );

        ctx.font = 'bold 22px Arial';
        ctx.textBaseline = 'alphabetic';

        drawLeftWrappedText(
            ctx,
            String(cardholder.address || '').toUpperCase(),
            438,
            465,
            500,
            30,
            2
        );

        ctx.textBaseline = 'middle';

        fitLeftText(
            ctx,
            String(cardholder.position || '').toUpperCase(),
            620,
            555,
            240,
            24,
            16,
            'Arial',
            'bold'
        );

        ctx.textBaseline = 'alphabetic';
    }

    async function drawSeniorCitizenBack(canvas, cardholder) {
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath(cardholder, 'back')
        );

        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            drawMissingTemplate(ctx, cardholder, 'back');
        }

        ctx.fillStyle = '#000000';

        fitCenteredText(
            ctx,
            cardholder.birthday,
            275,
            195,
            390,
            28,
            18,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            cardholder.age,
            735,
            195,
            390,
            28,
            18,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            String(cardholder.contact_name || '').toUpperCase(),
            506,
            319,
            500,
            28,
            18,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            cardholder.emergency_contact_number,
            506,
            450,
            500,
            28,
            18,
            'Arial',
            'bold'
        );

        ctx.textAlign = 'right';
        ctx.textBaseline = 'alphabetic';
        ctx.font = 'bold 28px Arial';

        ctx.fillText(cardholder.id_no || '', 870, 610);

        ctx.textAlign = 'left';
    }

    async function drawSangguniangKabataanFront(canvas, cardholder) {
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath(cardholder, 'front')
        );

        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            drawMissingTemplate(ctx, cardholder, 'front');
        }

        const photo = await loadPhoto(cardholder.photo_url);

        const photoCenterX = 223;
        const photoCenterY = 298;
        const photoRadius = 137;

        if (photo) {
            drawCircularCroppedImage(
                ctx,
                photo,
                photoCenterX,
                photoCenterY,
                photoRadius
            );
        } else {
            drawWhiteCirclePlaceholder(
                ctx,
                photoCenterX,
                photoCenterY,
                photoRadius
            );
        }

        ctx.fillStyle = '#ffffff';

        fitLeftText(
            ctx,
            cardholder.id_no,
            560,
            214,
            300,
            34,
            20,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            String(cardholder.name || '').toUpperCase(),
            704,
            292,
            310,
            27,
            16,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            cardholder.cellphone_no,
            704,
            362,
            310,
            26,
            16,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            String(cardholder.position || '').toUpperCase(),
            704,
            433,
            310,
            26,
            16,
            'Arial',
            'bold'
        );

        ctx.font = 'bold 23px Arial';

        drawCenteredWrappedText(
            ctx,
            String(cardholder.address || '').toUpperCase(),
            750,
            530,
            400,
            28,
            2
        );
    }

    async function drawSangguniangKabataanBack(canvas, cardholder) {
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath(cardholder, 'back')
        );

        if (template) {
            ctx.drawImage(template, 0, 0, CARD_W, CARD_H);
        } else {
            drawMissingTemplate(ctx, cardholder, 'back');
        }

        ctx.fillStyle = '#000000';

        fitCenteredText(
            ctx,
            cardholder.birthday,
            300,
            232,
            340,
            29,
            18,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            cardholder.age,
            749,
            232,
            340,
            29,
            18,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            String(cardholder.contact_name || '').toUpperCase(),
            543,
            383,
            420,
            27,
            16,
            'Arial',
            'bold'
        );

        fitCenteredText(
            ctx,
            cardholder.emergency_contact_number,
            543,
            538,
            420,
            28,
            17,
            'Arial',
            'bold'
        );

        ctx.textAlign = 'right';
        ctx.textBaseline = 'alphabetic';
        ctx.font = 'bold 28px Arial';
        ctx.fillStyle = '#ffffff';

        ctx.fillText(cardholder.id_no || '', 870, 610);

        ctx.textAlign = 'left';
        ctx.textBaseline = 'alphabetic';
    }

    async function drawCard(canvas, cardholder, requestedSide) {
        canvas.width = CARD_W;
        canvas.height = CARD_H;

        const slug = getCardTypeSlug(cardholder);

        if (slug === 'sangguniang-kabataan') {
            if (requestedSide === 'back') {
                await drawSangguniangKabataanBack(
                    canvas,
                    cardholder
                );
            } else {
                await drawSangguniangKabataanFront(
                    canvas,
                    cardholder
                );
            }

            return;
        }

        if (requestedSide === 'back') {
            await drawSeniorCitizenBack(canvas, cardholder);
        } else {
            await drawSeniorCitizenFront(canvas, cardholder);
        }
    }

    async function renderAll() {
        const slots = Array.from(
            document.querySelectorAll(
                '.card-slot[data-cardholder-id]'
            )
        );

        const cardholderMap = new Map(
            payload.map((cardholder) => [
                String(cardholder.id),
                cardholder
            ])
        );

        for (const slot of slots) {
            const cardholder = cardholderMap.get(
                String(slot.dataset.cardholderId)
            );

            const canvas = slot.querySelector('canvas');

            if (!cardholder || !canvas) {
                continue;
            }

            await drawCard(canvas, cardholder, side);
        }

        printButton.disabled = false;
        printButton.textContent = 'Print / Save as PDF';

        if (notice) {
            notice.textContent =
                'All IDs are ready. Use Letter, Landscape, '
                + '100% scale, no margins, and enable background graphics.';
        }
    }

    printButton.addEventListener(
        'click',
        function () {
            window.print();
        }
    );

    renderAll().catch(function (error) {
        console.error('Batch ID rendering failed.', error);

        if (notice) {
            notice.textContent =
                'Some IDs could not be rendered. '
                + 'Open the browser console for details.';
        }

        printButton.disabled = false;
        printButton.textContent = 'Print Anyway';
    });
})();
