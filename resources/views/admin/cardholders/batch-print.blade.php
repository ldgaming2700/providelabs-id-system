<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch {{ ucfirst($side) }} IDs</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #d9dde3;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            padding: 12px 16px;
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        .toolbar strong {
            margin-right: auto;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 7px;
            padding: 10px 14px;
            background: #1d4ed8;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn.light {
            background: #e2e8f0;
            color: #172033;
        }

        .btn:disabled {
            opacity: 0.55;
            cursor: wait;
        }

        .notice {
            width: min(11in, calc(100% - 24px));
            margin: 14px auto;
            padding: 10px 12px;
            border-radius: 8px;
            background: #ffffff;
            color: #475569;
        }

        .print-area {
            padding: 0 0 24px;
        }

        .sheet {
            width: 11in;
            height: 8.5in;
            margin: 18px auto;
            padding: 0.15in;
            background: #ffffff;
            display: grid;
            grid-template-columns: repeat(3, 3.375in);
            grid-template-rows: repeat(3, 2.125in);
            justify-content: space-between;
            align-content: space-between;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(15, 23, 42, 0.15);
            break-after: page;
            page-break-after: always;
        }

        .sheet:last-child {
            break-after: auto;
            page-break-after: auto;
        }

        .card-slot,
        .empty-slot {
            width: 3.375in;
            height: 2.125in;
        }

        .card-slot {
            overflow: hidden;
            background: transparent;
        }

        .card-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        @page {
            size: Letter landscape;
            margin: 0;
        }

        @media print {
            html,
            body {
                width: 11in;
                margin: 0;
                padding: 0;
                background: #ffffff;
            }

            .toolbar,
            .notice {
                display: none !important;
            }

            .print-area {
                padding: 0;
                margin: 0;
            }

            .sheet {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong>
            {{ $cardholders->count() }}
            selected cardholder(s) —
            {{ ucfirst($side) }} IDs
        </strong>

        <a
            href="{{ route('admin.cardholders.manage') }}"
            class="btn light"
        >
            Back to Admin
        </a>

        <button
            type="button"
            class="btn"
            id="print-button"
            disabled
        >
            Preparing IDs...
        </button>
    </div>

    <div id="render-notice" class="notice">
        Rendering {{ $cardholders->count() }} ID card(s).
        Please wait until the Print / Save PDF button becomes available.
    </div>

    <div
        class="print-area"
        id="print-area"
        data-side="{{ $side }}"
    >
        @foreach ($cardholders->chunk(9) as $pageIndex => $pageCardholders)
            <section
                class="sheet"
                aria-label="Print sheet {{ $pageIndex + 1 }}"
            >
                @foreach ($pageCardholders as $cardholder)
                    <div
                        class="card-slot"
                        data-cardholder-id="{{ $cardholder->id }}"
                    >
                        <canvas
                            class="card-canvas"
                            width="1011"
                            height="638"
                            aria-label="{{ $side }} ID for {{ $cardholder->name }}"
                        ></canvas>
                    </div>
                @endforeach

                @for ($blank = $pageCardholders->count(); $blank < 9; $blank++)
                    <div class="empty-slot" aria-hidden="true"></div>
                @endfor
            </section>
        @endforeach
    </div>

    <script type="application/json" id="batch-cardholder-json">{!! json_encode(
        $payload,
        JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) !!}</script>

    <script src="{{ asset('assets/admin-batch-card-print.js') }}"></script>
</body>
</html>
