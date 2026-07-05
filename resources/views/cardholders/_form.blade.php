@csrf
<div class="form-grid">
    <div class="field">
        <label for="card_type_id">Card Type</label>
        <select id="card_type_id" name="card_type_id" required>
            <option value="">Select card type</option>
            @foreach ($cardTypes as $type)
                <option value="{{ $type->id }}" @selected(old('card_type_id', $cardholder->card_type_id) == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        @error('card_type_id') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="id_no">ID NO</label>
        <input id="id_no" name="id_no" value="{{ old('id_no', $cardholder->id_no) }}" placeholder="26-00320" required>
        @error('id_no') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="field full">
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $cardholder->name) }}" required>
        @error('name') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="field">
        <label for="sc_id">SC ID / Card Reference Number</label>
        <input id="sc_id" name="sc_id" value="{{ old('sc_id', $cardholder->sc_id) }}">
    </div>

    <div class="field">
        <label for="philhealth">PhilHealth</label>
        <input id="philhealth" name="philhealth" value="{{ old('philhealth', $cardholder->philhealth) }}">
    </div>

    <div class="field">
        <label for="cellphone_no">Cellphone No.</label>
        <input id="cellphone_no" name="cellphone_no" value="{{ old('cellphone_no', $cardholder->cellphone_no) }}">
    </div>

    <div class="field">
        <label for="position">Position</label>
        <input id="position" name="position" value="{{ old('position', $cardholder->position) }}">
    </div>

    <div class="field">
        <label for="birthday">Birthday</label>
        <input id="birthday" name="birthday" type="date" value="{{ old('birthday', optional($cardholder->birthday)->format('Y-m-d')) }}">
    </div>

    <div class="field">
        <label>Age</label>
        <input value="{{ $cardholder->age ?? 'Auto-calculated after saving' }}" disabled>
    </div>

    <div class="field full">
        <label for="address">Address</label>
        <textarea id="address" name="address">{{ old('address', $cardholder->address) }}</textarea>
    </div>

    <div class="field">
        <label for="contact_name">Contact Person</label>
        <input id="contact_name" name="contact_name" value="{{ old('contact_name', $cardholder->contact_name) }}">
    </div>

    <div class="field">
        <label for="emergency_contact_number">Emergency Contact Number</label>
        <input id="emergency_contact_number" name="emergency_contact_number" value="{{ old('emergency_contact_number', $cardholder->emergency_contact_number) }}">
    </div>

    <div class="field">
        <label for="relationship">Relationship</label>
        <input id="relationship" name="relationship" value="{{ old('relationship', $cardholder->relationship) }}">
    </div>

    <div class="field">
        <label for="photo_upload">Upload Photo</label>
        <input id="photo_upload" name="photo_upload" type="file" accept="image/*">
        @error('photo_upload') <div class="error">{{ $message }}</div> @enderror
    </div>

    <div class="field full">
        <label>Take Photo using Phone/Webcam</label>
        <input type="hidden" name="captured_photo" value="">
        <div class="camera-box" data-camera-root>
            <div class="actions-row" style="margin-bottom: 12px;">
                <button type="button" class="btn secondary" data-camera-start>Open Camera</button>
                <button type="button" class="btn" data-camera-capture disabled>Capture Photo</button>
                <button type="button" class="btn light" data-camera-stop disabled>Stop Camera</button>
            </div>
            <video data-camera-video autoplay playsinline hidden></video>
            <canvas data-camera-canvas hidden></canvas>
            @if ($cardholder->photo_url)
                <p>Current photo:</p>
                <img src="{{ $cardholder->photo_url }}" alt="Current photo" style="width: 180px; height: 180px; object-fit: cover;">
            @endif
            <p>Captured preview:</p>
            <img data-camera-preview hidden alt="Captured photo preview" style="width: 180px; height: 180px; object-fit: cover;">
        </div>
    </div>
</div>

<div class="actions-row" style="margin-top: 20px;">
    <button type="submit" class="btn">Save Record</button>
    <a href="{{ route('cardholders.index') }}" class="btn light">Cancel</a>
</div>

<script src="{{ asset('assets/camera-capture.js') }}"></script>
