<x-layouts.app title="Dashboard - ProvideLabs ID System">
    <div class="page-title">
        <div>
            <h1>Dashboard</h1>
            <p>Registration, card generation, printing, and release monitoring.</p>
        </div>
        <a href="{{ route('cardholders.create') }}" class="btn">Register New Cardholder</a>
    </div>

    <div class="grid grid-3" style="margin-bottom: 20px;">
        <div class="card stat"><span>Total Records</span><strong>{{ $totalRecords }}</strong></div>
        <div class="card stat"><span>Pending</span><strong>{{ $pending }}</strong></div>
        <div class="card stat"><span>Generated</span><strong>{{ $generated }}</strong></div>
        <div class="card stat"><span>Printed</span><strong>{{ $printed }}</strong></div>
        <div class="card stat"><span>Released</span><strong>{{ $released }}</strong></div>
        <div class="card stat"><span>Photo Placeholders</span><strong>{{ $placeholders }}</strong></div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2>Records by Card Type</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Card Type</th><th>Records</th></tr></thead>
                    <tbody>
                        @foreach ($cardTypes as $type)
                            <tr><td>{{ $type->name }}</td><td>{{ $type->cardholders_count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <h2>Recent Records</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID No</th><th>Name</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($recentCardholders as $cardholder)
                            <tr>
                                <td><a href="{{ route('cardholders.show', $cardholder) }}">{{ $cardholder->id_no }}</a></td>
                                <td>{{ $cardholder->name }}</td>
                                <td><span class="badge {{ $cardholder->status }}">{{ str_replace('_', ' ', strtoupper($cardholder->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
