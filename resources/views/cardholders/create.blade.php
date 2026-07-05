<x-layouts.app title="Register Cardholder - ProvideLabs ID System">
    <div class="page-title">
        <div><h1>Register New Cardholder</h1><p>Encode details, capture or upload a photo, then generate the card.</p></div>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('cardholders.store') }}" enctype="multipart/form-data">
            @include('cardholders._form')
        </form>
    </div>
</x-layouts.app>
