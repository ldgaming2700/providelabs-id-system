<x-layouts.app title="Batch Import Cardholders">
    <div class="page-title">
        <div>
            <h1>Batch Import Cardholders</h1>
            <p>Upload a CSV database and an optional ZIP file of photos.</p>
        </div>

        <a href="{{ route('cardholders.index') }}" class="btn light">Back to Cardholders</a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('cardholders.import.store') }}" enctype="multipart/form-data" class="grid">
            @csrf

            <div class="field">
                <label for="card_type_id">Card Type</label>
                <select id="card_type_id" name="card_type_id" required>
                    <option value="">Select card type</option>
                    @foreach (\App\Models\CardType::where('is_active', true)->orderBy('name')->get() as $type)
                        <option value="{{ $type->id }}" @selected(old('card_type_id') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('card_type_id') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="csv_file">CSV File</label>
                <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required>
                @error('csv_file') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="photos_zip">Photos ZIP</label>
                <input id="photos_zip" name="photos_zip" type="file" accept=".zip">
                <small style="color: #64748b;">
                    Optional. Photo filenames should match ID numbers, for example 26-00320.jpg.
                </small>
                @error('photos_zip') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="mode">If ID NO already exists</label>
                <select id="mode" name="mode" required>
                    <option value="skip" @selected(old('mode') === 'skip')>Skip existing records</option>
                    <option value="update" @selected(old('mode') === 'update')>Update existing records</option>
                </select>
                @error('mode') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="card" style="background: #f8fafc;">
                <h3>Expected CSV Headers</h3>
                <p style="margin-bottom: 0;">
                    ID NO, NAME, SC ID, PHILHEALTH, CELLPHONE NO, ADDRESS, POSITION,
                    BIRTHDAY, CONTACT NAME, EMERGENCY CONTACT NUMBER, RELATIONSHIP
                </p>
            </div>

            <div class="actions-row">
                <button type="submit" class="btn">Start Import</button>
            </div>
        </form>
    </div>
</x-layouts.app>