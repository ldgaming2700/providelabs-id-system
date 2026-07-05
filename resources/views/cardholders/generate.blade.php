<x-layouts.app title="Generate ID - {{ $cardholder->id_no }}">
    <div class="page-title">
        <div><h1>Generate ID</h1><p>{{ $cardholder->id_no }} — {{ $cardholder->name }}</p></div>
        <div class="actions-row">
            <a href="{{ route('cardholders.show', $cardholder) }}" class="btn light">Back to Record</a>
            <form method="POST" action="{{ route('cardholders.mark-generated', $cardholder) }}">
                @csrf
                <button class="btn secondary">Mark Generated</button>
            </form>
        </div>
    </div>

    <div class="card" data-card-generator>
        <script type="application/json" data-cardholder-json>
            {!! json_encode([
                'id_no' => $cardholder->id_no,
                'name' => $cardholder->name,
                'sc_id' => $cardholder->sc_id,
                'philhealth' => $cardholder->philhealth,
                'cellphone_no' => $cardholder->cellphone_no,
                'address' => $cardholder->address,
                'position' => $cardholder->position,
                'birthday' => optional($cardholder->birthday)->format('m/d/Y'),
                'age' => $cardholder->age,
                'contact_name' => $cardholder->contact_name,
                'emergency_contact_number' => $cardholder->emergency_contact_number,
                'relationship' => $cardholder->relationship,
                'photo_url' => $cardholder->photo_url,
                'photo_status' => $cardholder->photo_status,
                'card_type' => [
                    'name' => $cardholder->cardType->name,
                    'front_title' => $cardholder->cardType->front_title,
                    'back_title' => $cardholder->cardType->back_title,
                    'primary_color' => $cardholder->cardType->primary_color,
                    'secondary_color' => $cardholder->cardType->secondary_color,
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>

        <p>
            Review the front and back card previews. Download each side as PNG.
            @if ($cardholder->photo_status !== 'uploaded')
                <strong>This record has no uploaded photo, so the preview will use a white placeholder.</strong>
            @endif
        </p>

        <div class="card-preview-grid">
            <div>
                <h2>Front</h2>
                <canvas id="front-card" class="id-canvas"></canvas>
                <div class="actions-row" style="margin-top: 12px;">
                    <button class="btn" type="button" data-download-front>Download Front PNG</button>
                </div>
            </div>
            <div>
                <h2>Back</h2>
                <canvas id="back-card" class="id-canvas"></canvas>
                <div class="actions-row" style="margin-top: 12px;">
                    <button class="btn" type="button" data-download-back>Download Back PNG</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/card-generator.js') }}"></script>
</x-layouts.app>
