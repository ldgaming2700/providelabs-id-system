<x-layouts.app title="Cardholders - ProvideLabs ID System">
    <div class="page-title">
        <div>
            <h1>Cardholders</h1>
            <p>Search, edit, generate, print, and release IDs.</p>
        </div>

        <div class="actions-row">
            <a href="{{ route('cardholders.import') }}" class="btn secondary">Batch Import</a>
            <a href="{{ route('cardholders.create') }}" class="btn">Register New</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 18px;">
        <form method="GET" class="form-grid">
            <div class="field"><label for="search">Search</label><input id="search" name="search" value="{{ request('search') }}" placeholder="ID no, name, SC ID"></div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="card_type_id">Card Type</label>
                <select id="card_type_id" name="card_type_id">
                    <option value="">All card types</option>
                    @foreach ($cardTypes as $type)
                        <option value="{{ $type->id }}" @selected((int) request('card_type_id') === $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="justify-content: end;"><button class="btn" type="submit">Apply Filters</button></div>
        </form>
    </div>

    <div class="card table-wrap">
        <table>
            <thead><tr><th>ID No</th><th>Name</th><th>Card Type</th><th>Encoded By</th><th>Photo</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($cardholders as $cardholder)
                    <tr>
                        <td><strong>{{ $cardholder->id_no }}</strong></td>
                        <td>{{ $cardholder->name }}</td>
                        <td>{{ $cardholder->cardType->name }}</td>
                        <td>{{ $cardholder->encoder->name ?? 'Unknown' }}</td>
                        <td>{{ $cardholder->photo_status === 'uploaded' ? 'Uploaded' : 'Placeholder' }}</td>
                        <td><span class="badge {{ $cardholder->status }}">{{ str_replace('_', ' ', strtoupper($cardholder->status)) }}</span></td>
                        <td>
                            <div class="actions-row">
                                <a href="{{ route('cardholders.show', $cardholder) }}">View</a>
                                <a href="{{ route('cardholders.edit', $cardholder) }}">Edit</a>
                                <a href="{{ route('cardholders.generate', $cardholder) }}">Generate</a>
                                @if (auth()->user()?->role === 'admin')
                                <form
                                    method="POST"
                                    action="{{ route('cardholders.destroy', $cardholder) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete {{ $cardholder->id_no }} - {{ $cardholder->name }}? This cannot be undone.');"
                                    >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="link-danger"
                                        style="background: none; border: none; padding: 0; cursor: pointer;"
                                        >
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">
        {{ $cardholders->onEachSide(1)->links('pagination::simple-default') }}  
        </div>
    </div>
</x-layouts.app>
