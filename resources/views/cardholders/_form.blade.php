@csrf

<div class="form-grid">
    {{-- Card Type --}}
    <div class="field">
        <label for="card_type_id">Card Type</label>

        <select
            id="card_type_id"
            name="card_type_id"
            required
        >
            <option value="">Select card type</option>

            @foreach ($cardTypes as $type)
                <option
                    value="{{ $type->id }}"
                    data-slug="{{ $type->slug }}"
                    @selected(
                        old(
                            'card_type_id',
                            $cardholder->card_type_id
                        ) == $type->id
                    )
                >
                    {{ $type->name }}
                </option>
            @endforeach
        </select>

        @error('card_type_id')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Automatically generated ID number --}}
    <div class="field">
        <label for="id_no_display">ID No.</label>

        <input
            id="id_no_display"
            type="text"
            value="{{ $cardholder->exists
                ? $cardholder->id_no
                : 'Auto-generated after saving' }}"
            disabled
        >

        <small style="color: #64748b;">
            The ID number is generated automatically.
        </small>
    </div>

    {{-- Name --}}
    <div class="field full">
        <label for="name">Name</label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $cardholder->name) }}"
            required
            data-name-check-url="{{ route('cardholders.check-name') }}"
            data-current-cardholder-id="{{ $cardholder->id }}"
        >

        <div
            id="name-warning"
            class="error"
            hidden
        ></div>

        @error('name')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{--
        Senior Citizen field.

        This field is hidden when Sangguniang Kabataan
        is selected.
    --}}
    <div
        class="field"
        id="sc-id-field"
    >
        <label for="sc_id">
            SC ID / Card Reference Number
        </label>

        <input
            id="sc_id"
            name="sc_id"
            type="text"
            value="{{ old('sc_id', $cardholder->sc_id) }}"
        >

        @error('sc_id')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{--
        Senior Citizen field.

        This field is hidden when Sangguniang Kabataan
        is selected.
    --}}
    <div
        class="field"
        id="philhealth-field"
    >
        <label for="philhealth">
            PhilHealth
        </label>

        <input
            id="philhealth"
            name="philhealth"
            type="text"
            value="{{ old(
                'philhealth',
                $cardholder->philhealth
            ) }}"
        >

        @error('philhealth')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Contact Number --}}
    <div class="field">
        <label for="cellphone_no">
            Contact No.
        </label>

        <input
            id="cellphone_no"
            name="cellphone_no"
            type="text"
            value="{{ old(
                'cellphone_no',
                $cardholder->cellphone_no
            ) }}"
        >

        @error('cellphone_no')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Position --}}
    <div class="field">
        <label for="position">
            Position
        </label>

        <input
            id="position"
            name="position"
            type="text"
            value="{{ old(
                'position',
                $cardholder->position
            ) }}"
        >

        @error('position')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{--
        Sangguniang Kabataan-only field.

        This is optional and is used only for internal
        tracking. It will not appear on the generated ID.
    --}}
    <div
        class="field full"
        id="school-field"
        hidden
    >
        <label for="school">
            School

            <small style="color: #64748b;">
                Optional — for internal tracking only
            </small>
        </label>

        <input
            id="school"
            name="school"
            type="text"
            value="{{ old(
                'school',
                $cardholder->school
            ) }}"
            placeholder="Enter school name"
        >

        @error('school')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Address --}}
    <div class="field full">
        <label for="address">
            Address
        </label>

        <textarea
            id="address"
            name="address"
            rows="3"
        >{{ old('address', $cardholder->address) }}</textarea>

        @error('address')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Birthdate --}}
    <div class="field">
        <label for="birthday">
            Birthdate
        </label>

        <input
            id="birthday"
            name="birthday"
            type="date"
            value="{{ old(
                'birthday',
                $cardholder->birthday
                    ? $cardholder->birthday->format('Y-m-d')
                    : ''
            ) }}"
        >

        @error('birthday')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Automatically calculated age --}}
    <div class="field">
        <label for="age_display">
            Age
        </label>

        <input
            id="age_display"
            type="text"
            value="{{ $cardholder->age
                ?? 'Auto-calculated from birthdate' }}"
            disabled
        >

        <small style="color: #64748b;">
            Age is calculated automatically from the birthdate.
        </small>
    </div>

    {{-- Emergency Contact Person --}}
    <div class="field">
        <label for="contact_name">
            Contact Person
        </label>

        <input
            id="contact_name"
            name="contact_name"
            type="text"
            value="{{ old(
                'contact_name',
                $cardholder->contact_name
            ) }}"
        >

        @error('contact_name')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Emergency Contact Number --}}
    <div class="field">
        <label for="emergency_contact_number">
            Emergency No.
        </label>

        <input
            id="emergency_contact_number"
            name="emergency_contact_number"
            type="text"
            value="{{ old(
                'emergency_contact_number',
                $cardholder->emergency_contact_number
            ) }}"
        >

        @error('emergency_contact_number')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{--
        This remains available for both card types.

        Only SC ID and PhilHealth are hidden for
        Sangguniang Kabataan.
    --}}
    <div class="field">
        <label for="relationship">
            Relationship
        </label>

        <input
            id="relationship"
            name="relationship"
            type="text"
            value="{{ old(
                'relationship',
                $cardholder->relationship
            ) }}"
        >

        @error('relationship')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Photo Upload --}}
    <div class="field">
        <label for="photo_upload">
            Upload Photo
        </label>

        <input
            id="photo_upload"
            name="photo_upload"
            type="file"
            accept="image/jpeg,image/png,image/webp"
        >

        <small style="color: #64748b;">
            Accepted formats: JPG, PNG, or WebP.
        </small>

        @error('photo_upload')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- Camera Capture --}}
    <div class="field full">
        <label>
            Take Photo Using Phone or Webcam
        </label>

        <input
            type="hidden"
            name="captured_photo"
            value=""
        >

        <div
            class="camera-box"
            data-camera-root
        >
            <div
                class="actions-row"
                style="margin-bottom: 12px;"
            >
                <button
                    type="button"
                    class="btn secondary"
                    data-camera-start
                >
                    Open Camera
                </button>

                <button
                    type="button"
                    class="btn"
                    data-camera-capture
                    disabled
                >
                    Capture Photo
                </button>

                <button
                    type="button"
                    class="btn light"
                    data-camera-stop
                    disabled
                >
                    Stop Camera
                </button>
            </div>

            <video
                data-camera-video
                autoplay
                playsinline
                hidden
            ></video>

            <canvas
                data-camera-canvas
                hidden
            ></canvas>

            @if ($cardholder->photo_url)
                <div style="margin-top: 16px;">
                    <p>
                        Current photo:
                    </p>

                    <img
                        src="{{ $cardholder->photo_url }}"
                        alt="Current cardholder photo"
                        style="
                            width: 180px;
                            height: 180px;
                            object-fit: cover;
                            border-radius: 8px;
                        "
                    >
                </div>
            @endif

            <div style="margin-top: 16px;">
                <p>
                    Captured preview:
                </p>

                <img
                    data-camera-preview
                    hidden
                    alt="Captured photo preview"
                    style="
                        width: 180px;
                        height: 180px;
                        object-fit: cover;
                        border-radius: 8px;
                    "
                >
            </div>
        </div>

        @error('captured_photo')
            <div class="error">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

<div
    class="actions-row"
    style="margin-top: 20px;"
>
    <button
        type="submit"
        class="btn"
    >
        Save Record
    </button>

    @if (auth()->user()?->role === 'admin')
        <a
            href="{{ route('cardholders.index') }}"
            class="btn light"
        >
            Cancel
        </a>
    @endif
</div>

<script src="{{ asset('assets/camera-capture.js') }}"></script>
<script src="{{ asset('assets/duplicate-name-check.js') }}"></script>
<script src="{{ asset('assets/card-type-fields.js') }}"></script>