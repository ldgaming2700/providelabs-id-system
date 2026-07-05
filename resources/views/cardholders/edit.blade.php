<x-layouts.app title="Edit Cardholder - ProvideLabs ID System">
    <div class="page-title">
        <div><h1>Edit Cardholder</h1><p>{{ $cardholder->id_no }} — {{ $cardholder->name }}</p></div>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('cardholders.update', $cardholder) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('cardholders._form')
        </form>
    </div>
</x-layouts.app>
