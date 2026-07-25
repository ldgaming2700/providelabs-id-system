(function () {
    const root = document.querySelector('[data-card-generator]');

    if (!root) {
        return;
    }

    const payloadElement = root.querySelector('[data-cardholder-json]');

    if (!payloadElement) {
        console.error('Cardholder JSON payload was not found.');
        return;
    }

    let payload;

    try {
        payload = JSON.parse(payloadElement.textContent);
    } catch (error) {
        console.error('Unable to parse cardholder information.', error);
        return;
    }

    const frontCanvas = document.getElementById('front-card');
    const backCanvas = document.getElementById('back-card');
    const frontDownload = document.querySelector('[data-download-front]');
    const backDownload = document.querySelector('[data-download-back]');

    if (!frontCanvas || !backCanvas) {
        console.error('Front or back card canvas was not found.');
        return;
    }

    const CARD_W = 1011;
    const CARD_H = 638;

    function setupCanvas(canvas) {
        canvas.width = CARD_W;
        canvas.height = CARD_H;
    }

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function getCardTypeName() {
        if (
            payload.card_type &&
            typeof payload.card_type === 'object'
        ) {
            return payload.card_type.name || '';
        }

        return (
            payload.card_type_name ||
            payload.card_type ||
            ''
        );
    }

    function getCardTypeSlug() {
        if (payload.card_type_slug) {
            return slugify(payload.card_type_slug);
        }

        if (
            payload.card_type &&
            typeof payload.card_type === 'object'
        ) {
            if (payload.card_type.slug) {
                return slugify(payload.card_type.slug);
            }

            if (payload.card_type.name) {
                return slugify(payload.card_type.name);
            }
        }

        return slugify(
            payload.card_type_name ||
            payload.card_type ||
            'senior-citizen-card'
        );
    }

    function getTemplatePath(side) {
        return (
            '/assets/card-templates/' +
            getCardTypeSlug() +
            '/' +
            side +
            '.png'
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

            /*
                Do not set crossOrigin here.

                The protected Laravel photo route is on the same domain
                and uses the logged-in user's session.
            */
            image.src = url;
        });
    }

    function drawMissingTemplate(ctx, side) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, CARD_W, CARD_H);

        ctx.fillStyle = '#b91c1c';
        ctx.font = 'bold 30px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        ctx.fillText(
            'Missing ' + side + ' template for ' + getCardTypeName(),
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
        ctx.arc(
            centerX,
            centerY,
            radius,
            0,
            Math.PI * 2
        );
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
        ctx.arc(
            centerX,
            centerY,
            radius,
            0,
            Math.PI * 2
        );
        ctx.closePath();
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
                (fontWeight || 'bold') +
                ' ' +
                fontSize +
                'px ' +
                (fontFamily || 'Arial');

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
                (fontWeight || 'bold') +
                ' ' +
                fontSize +
                'px ' +
                (fontFamily || 'Arial');

            if (ctx.measureText(value).width <= maxWidth) {
                break;
            }

            fontSize -= 1;
        }

        const previousAlignment = ctx.textAlign;
        const previousBaseline = ctx.textBaseline;

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(value, centerX, centerY);

        ctx.textAlign = previousAlignment;
        ctx.textBaseline = previousBaseline;
    }

    function buildWrappedLines(
        ctx,
        text,
        maxWidth,
        maximumLines
    ) {
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
                ctx.measureText(testLine).width > maxWidth &&
                currentLine
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
            const limitedLines = lines.slice(0, maximumLines);
            let finalLine = limitedLines[maximumLines - 1];

            while (
                finalLine.length > 0 &&
                ctx.measureText(finalLine + '...').width > maxWidth
            ) {
                finalLine = finalLine.slice(0, -1);
            }

            limitedLines[maximumLines - 1] =
                finalLine.trim() + '...';

            return limitedLines;
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

        if (lines.length === 0) {
            return;
        }

        const previousAlignment = ctx.textAlign;
        const previousBaseline = ctx.textBaseline;

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const totalHeight =
            (lines.length - 1) * lineHeight;

        const firstCenterY =
            centerY - totalHeight / 2;

        lines.forEach(function (line, index) {
            ctx.fillText(
                line,
                centerX,
                firstCenterY + index * lineHeight
            );
        });

        ctx.textAlign = previousAlignment;
        ctx.textBaseline = previousBaseline;
    }

    /*
    |--------------------------------------------------------------------------
    | Senior Citizen Card
    |--------------------------------------------------------------------------
    */

    async function drawSeniorCitizenFront() {
        const ctx = frontCanvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath('front')
        );

        if (template) {
            ctx.drawImage(
                template,
                0,
                0,
                CARD_W,
                CARD_H
            );
        } else {
            drawMissingTemplate(ctx, 'front');
        }

        const photo = await loadPhoto(payload.photo_url);

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
            payload.id_no,
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
            String(payload.name || '').toUpperCase(),
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
            payload.sc_id,
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
            payload.philhealth,
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
            payload.cellphone_no,
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
            String(payload.address || '').toUpperCase(),
            438,
            465,
            500,
            30,
            2
        );

        ctx.textBaseline = 'middle';

        fitLeftText(
            ctx,
            String(payload.position || '').toUpperCase(),
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

    async function drawSeniorCitizenBack() {
        const ctx = backCanvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath('back')
        );

        if (template) {
            ctx.drawImage(
                template,
                0,
                0,
                CARD_W,
                CARD_H
            );
        } else {
            drawMissingTemplate(ctx, 'back');
        }

        ctx.fillStyle = '#000000';

        fitCenteredText(
            ctx,
            payload.birthday,
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
            payload.age,
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
            String(payload.contact_name || '').toUpperCase(),
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
            payload.emergency_contact_number,
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

        ctx.fillText(
            payload.id_no || '',
            870,
            610
        );

        ctx.textAlign = 'left';
    }

    /*
    |--------------------------------------------------------------------------
    | Sangguniang Kabataan Card
    |--------------------------------------------------------------------------
    */

    async function drawSangguniangKabataanFront() {
        const ctx = frontCanvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath('front')
        );

        if (template) {
            ctx.drawImage(
                template,
                0,
                0,
                CARD_W,
                CARD_H
            );
        } else {
            drawMissingTemplate(ctx, 'front');
        }

        /*
            Circular photo area inside the teal border.
        */
        const photoCenterX = 223;
        const photoCenterY = 298;
        const photoRadius = 137;

        const photo = await loadPhoto(payload.photo_url);

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

        /*
            ID number on the colored header.
        */
        ctx.fillStyle = '#ffffff';

        fitLeftText(
            ctx,
            payload.id_no,
            560,
            214,
            300,
            34,
            20,
            'Arial',
            'bold'
        );

        /*
            White values inside the black front fields.
        */
        fitCenteredText(
            ctx,
            String(payload.name || '').toUpperCase(),
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
            payload.cellphone_no,
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
            String(payload.position || '').toUpperCase(),
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
            String(payload.address || '').toUpperCase(),
            750,
            530,
            400,
            28,
            2
        );
    }

    async function drawSangguniangKabataanBack() {
        const ctx = backCanvas.getContext('2d');

        ctx.clearRect(0, 0, CARD_W, CARD_H);

        const template = await loadImage(
            getTemplatePath('back')
        );

        if (template) {
            ctx.drawImage(
                template,
                0,
                0,
                CARD_W,
                CARD_H
            );
        } else {
            drawMissingTemplate(ctx, 'back');
        }

        ctx.fillStyle = '#000000';

        /*
            Birthdate: left white field.
        */
        fitCenteredText(
            ctx,
            payload.birthday,
            300,
            232,
            340,
            29,
            18,
            'Arial',
            'bold'
        );

        /*
            Age: right white field.
        */
        fitCenteredText(
            ctx,
            payload.age,
            749,
            232,
            340,
            29,
            18,
            'Arial',
            'bold'
        );

        /*
            Emergency contact name.
        */
        fitCenteredText(
            ctx,
            String(payload.contact_name || '').toUpperCase(),
            543,
            383,
            420,
            27,
            16,
            'Arial',
            'bold'
        );

        /*
            Emergency contact number.
        */
        fitCenteredText(
            ctx,
            payload.emergency_contact_number,
            543,
            538,
            420,
            28,
            17,
            'Arial',
            'bold'
        );
        /*
            ID number at the same bottom-right position
            used by the Senior Citizen back.
        */
        ctx.textAlign = 'right';
        ctx.textBaseline = 'alphabetic';
        ctx.font = 'bold 28px Arial';
        ctx.fillStyle = '#ffffff';

        ctx.fillText(
            payload.id_no || '',
            870,
            610
        );

        ctx.textAlign = 'left';
        ctx.textBaseline = 'alphabetic';
    }

    /*
    |--------------------------------------------------------------------------
    | Card Type Dispatcher
    |--------------------------------------------------------------------------
    */

    async function drawFront() {
        setupCanvas(frontCanvas);

        const slug = getCardTypeSlug();

        if (slug === 'sangguniang-kabataan') {
            await drawSangguniangKabataanFront();
            return;
        }

        await drawSeniorCitizenFront();
    }

    async function drawBack() {
        setupCanvas(backCanvas);

        const slug = getCardTypeSlug();

        if (slug === 'sangguniang-kabataan') {
            await drawSangguniangKabataanBack();
            return;
        }

        await drawSeniorCitizenBack();
    }

    function downloadCanvas(canvas, filename) {
        canvas.toBlob(
            function (blob) {
                if (!blob) {
                    alert('The ID image could not be generated.');
                    return;
                }

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = url;
                link.download = filename;

                document.body.appendChild(link);
                link.click();
                link.remove();

                URL.revokeObjectURL(url);
            },
            'image/png'
        );
    }

    if (frontDownload) {
        frontDownload.addEventListener(
            'click',
            function () {
                downloadCanvas(
                    frontCanvas,
                    String(payload.id_no || 'ID') +
                    '_FRONT.png'
                );
            }
        );
    }

    if (backDownload) {
        backDownload.addEventListener(
            'click',
            function () {
                downloadCanvas(
                    backCanvas,
                    String(payload.id_no || 'ID') +
                    '_BACK.png'
                );
            }
        );
    }

    Promise.all([
        drawFront(),
        drawBack()
    ]).catch(function (error) {
        console.error(
            'The card previews could not be generated.',
            error
        );
    });
})();