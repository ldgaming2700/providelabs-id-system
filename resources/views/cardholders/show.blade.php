<x-layouts.app title="{{ $cardholder->id_no }} - ProvideLabs ID System">
    <div class="page-title">
        <div><h1>{{ $cardholder->name }}</h1><p>{{ $cardholder->id_no }} · {{ $cardholder->cardType->name }}</p></div>
        <div class="actions-row">
            <a href="{{ route('cardholders.edit', $cardholder) }}" class="btn light">Edit</a>
            <a href="{{ route('cardholders.generate', $cardholder) }}" class="btn">Generate ID</a>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2>Cardholder Information</h2>
            <table>
                <tr><th>ID NO</th><td>{{ $cardholder->id_no }}</td></tr>
                <tr><th>Name</th><td>{{ $cardholder->name }}</td></tr>
                <tr><th>SC ID / Ref No</th><td>{{ $cardholder->sc_id ?: '-' }}</td></tr>
                <tr><th>PhilHealth</th><td>{{ $cardholder->philhealth ?: '-' }}</td></tr>
                <tr><th>Cellphone</th><td>{{ $cardholder->cellphone_no ?: '-' }}</td></tr>
                <tr><th>Birthday</th><td>{{ optional($cardholder->birthday)->format('m/d/Y') ?: '-' }}</td></tr>
                <tr><th>Age</th><td>{{ $cardholder->age ?? '-' }}</td></tr>
                <tr><th>Address</th><td>{{ $cardholder->address ?: '-' }}</td></tr>
                <tr><th>Contact Person</th><td>{{ $cardholder->contact_name ?: '-' }}</td></tr>
                <tr><th>Emergency No.</th><td>{{ $cardholder->emergency_contact_number ?: '-' }}</td></tr>
            </table>
        </div>

        <div class="card">
            <h2>Status</h2>
            <p><span class="badge {{ $cardholder->status }}">{{ str_replace('_', ' ', strtoupper($cardholder->status)) }}</span></p>
            <p><strong>Photo:</strong> {{ $cardholder->photo_status === 'uploaded' ? 'Uploaded' : 'White placeholder will be used' }}</p>
            @if ($cardholder->photo_url)
                <img src="{{ $cardholder->photo_url }}" alt="Photo" style="width: 220px; height: 220px; object-fit: cover; border-radius: 20px;">
            @endif
            <hr>
            <div class="actions-row">
                <form method="POST" action="{{ route('cardholders.mark-generated', $cardholder) }}">@csrf <button class="btn secondary">Mark Generated</button></form>
                <form method="POST" action="{{ route('cardholders.mark-printed', $cardholder) }}">@csrf <button class="btn">Mark Printed</button></form>
                <form method="POST" action="{{ route('cardholders.mark-released', $cardholder) }}">@csrf <button class="btn success">Mark Released</button></form>
                <form method="POST" action="{{ route('cardholders.mark-for-correction', $cardholder) }}">@csrf <button class="btn danger">For Correction</button></form>
            </div>
        </div>
    </div>
</x-layouts.app>
