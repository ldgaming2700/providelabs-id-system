<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardholder Management</title>

    <style>
        :root {
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
            background: #f4f6f8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f8;
        }

        .page {
            width: min(1500px, calc(100% - 32px));
            margin: 28px auto 60px;
        }

        .topbar,
        .filters,
        .batch-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .topbar {
            justify-content: space-between;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0;
            font-size: 28px;
        }

        .muted {
            color: #64748b;
        }

        .panel {
            background: #fff;
            border: 1px solid #dfe4ea;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .filters input,
        .filters select,
        .batch-bar select {
            min-height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 8px 10px;
            background: #fff;
        }

        .filters input[type="search"] {
            min-width: 280px;
            flex: 1 1 320px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 14px;
            border: 0;
            border-radius: 7px;
            background: #1d4ed8;
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn.secondary {
            background: #475569;
        }

        .btn.success {
            background: #15803d;
        }

        .btn.light {
            background: #e2e8f0;
            color: #172033;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1120px;
        }

        th,
        td {
            padding: 11px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
            white-space: nowrap;
        }

        th {
            background: #f8fafc;
            font-size: 13px;
            color: #475569;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        th a {
            color: inherit;
            text-decoration: none;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e2e8f0;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .batch-bar {
            margin-bottom: 14px;
        }

        .selected-count {
            font-weight: 700;
        }

        .pagination {
            margin-top: 16px;
        }

        @media (max-width: 700px) {
            .page {
                width: min(100% - 18px, 1500px);
                margin-top: 14px;
            }

            .filters > *,
            .batch-bar > * {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <div>
            <h1>Cardholder Management</h1>
            <div class="muted">Admin-only record management</div>
        </div>

        <div class="topbar">
            <a href="{{ route('cardholders.index') }}" class="btn light">
                Back to Cardholders
            </a>

            <a
                href="{{ route('admin.cardholders.export-csv') }}"
                class="btn success"
            >
                Download All Cardholders CSV
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert error">
            <strong>The batch update could not be completed.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <form
            method="GET"
            action="{{ route('admin.cardholders.manage') }}"
            class="filters"
        >
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search ID number, name, contact, position, address or school"
            >

            <select name="card_type_id">
                <option value="">All card types</option>

                @foreach ($cardTypes as $id => $name)
                    <option
                        value="{{ $id }}"
                        @selected((string) request('card_type_id') === (string) $id)
                    >
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">All statuses</option>

                @foreach ($statusOptions as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(request('status') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">

            <button type="submit" class="btn">
                Filter
            </button>

            <a
                href="{{ route('admin.cardholders.manage') }}"
                class="btn light"
            >
                Reset
            </a>
        </form>
    </div>

    @php
        $sortUrl = function (string $column) use ($sort, $direction) {
            $nextDirection =
                $sort === $column && $direction === 'asc'
                    ? 'desc'
                    : 'asc';

            $query = request()->query();

            $query['sort'] = $column;
            $query['direction'] = $nextDirection;
            unset($query['page']);

            return route('admin.cardholders.manage', $query);
        };

        $sortIndicator = function (string $column) use ($sort, $direction) {
            if ($sort !== $column) {
                return '↕';
            }

            return $direction === 'asc' ? '↑' : '↓';
        };
    @endphp

    <div class="panel">
        <form
            method="POST"
            action="{{ route('admin.cardholders.batch-status') }}"
            id="batch-status-form"
        >
            @csrf

            <div class="batch-bar">
                <span class="selected-count">
                    Selected:
                    <span id="selected-count">0</span>
                </span>

                <select name="status" required>
                    <option value="">Change status to...</option>

                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn secondary">
                    Apply to Selected
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>
                            <input
                                type="checkbox"
                                id="select-all"
                                aria-label="Select all records on this page"
                            >
                        </th>

                        <th>
                            <a href="{{ $sortUrl('id_no') }}">
                                ID Number {{ $sortIndicator('id_no') }}
                            </a>
                        </th>

                        <th>
                            <a href="{{ $sortUrl('name') }}">
                                Name {{ $sortIndicator('name') }}
                            </a>
                        </th>

                        <th>Card Type</th>

                        <th>
                            <a href="{{ $sortUrl('status') }}">
                                Status {{ $sortIndicator('status') }}
                            </a>
                        </th>

                        <th>
                            <a href="{{ $sortUrl('position') }}">
                                Position {{ $sortIndicator('position') }}
                            </a>
                        </th>

                        <th>Contact No.</th>

                        <th>
                            <a href="{{ $sortUrl('birthday') }}">
                                Birthdate {{ $sortIndicator('birthday') }}
                            </a>
                        </th>

                        <th>
                            <a href="{{ $sortUrl('created_at') }}">
                                Created {{ $sortIndicator('created_at') }}
                            </a>
                        </th>

                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse ($cardholders as $cardholder)
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    class="cardholder-checkbox"
                                    name="cardholder_ids[]"
                                    value="{{ $cardholder->id }}"
                                    aria-label="Select {{ $cardholder->name }}"
                                >
                            </td>

                            <td>
                                {{ $cardholder->id_no }}
                            </td>

                            <td>
                                <strong>{{ $cardholder->name }}</strong>
                            </td>

                            <td>
                                {{ $cardTypes[$cardholder->card_type_id] ?? '—' }}
                            </td>

                            <td>
                                <span class="status">
                                    {{
                                        $statusOptions[$cardholder->status]
                                        ?? ucfirst($cardholder->status ?? '')
                                    }}
                                </span>
                            </td>

                            <td>
                                {{ $cardholder->position ?: '—' }}
                            </td>

                            <td>
                                {{ $cardholder->cellphone_no ?: '—' }}
                            </td>

                            <td>
                                {{
                                    $cardholder->birthday
                                        ? \Carbon\Carbon::parse($cardholder->birthday)->format('Y-m-d')
                                        : '—'
                                }}
                            </td>

                            <td>
                                {{
                                    $cardholder->created_at
                                        ? \Carbon\Carbon::parse($cardholder->created_at)->format('Y-m-d H:i')
                                        : '—'
                                }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('cardholders.edit', $cardholder) }}"
                                    class="btn light"
                                >
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="muted">
                                No cardholder records matched the current filters.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="pagination">
            {{ $cardholders->links() }}
        </div>
    </div>
</div>

<script>
(function () {
    const form =
        document.getElementById('batch-status-form');

    const selectAll =
        document.getElementById('select-all');

    const checkboxes =
        Array.from(
            document.querySelectorAll('.cardholder-checkbox')
        );

    const selectedCount =
        document.getElementById('selected-count');

    function refreshCount() {
        const selected =
            checkboxes.filter(
                (checkbox) => checkbox.checked
            ).length;

        selectedCount.textContent = selected;

        if (selectAll) {
            selectAll.checked =
                checkboxes.length > 0
                && selected === checkboxes.length;

            selectAll.indeterminate =
                selected > 0
                && selected < checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener(
            'change',
            function () {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });

                refreshCount();
            }
        );
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener(
            'change',
            refreshCount
        );
    });

    if (form) {
        form.addEventListener(
            'submit',
            function (event) {
                const selected =
                    checkboxes.filter(
                        (checkbox) => checkbox.checked
                    ).length;

                const status =
                    form.querySelector(
                        'select[name="status"]'
                    );

                if (selected === 0) {
                    event.preventDefault();

                    alert(
                        'Please select at least one cardholder record.'
                    );

                    return;
                }

                if (!status || !status.value) {
                    event.preventDefault();

                    alert(
                        'Please choose the status to assign.'
                    );

                    return;
                }

                if (
                    !confirm(
                        'Update the status of '
                        + selected
                        + ' selected record(s)?'
                    )
                ) {
                    event.preventDefault();
                }
            }
        );
    }

    refreshCount();
})();
</script>
</body>
</html>
