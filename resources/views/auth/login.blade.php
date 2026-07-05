<x-layouts.app title="Login - ProvideLabs ID System">
    <div class="login-shell">
        <div class="card login-card">
            <div class="brand" style="margin-bottom: 20px;">
                <span class="brand-mark"></span>
                <span>ProvideLabs ID System</span>
            </div>
            <h1 style="margin-top: 0;">Secure Login</h1>
            <p style="color: #64748b;">Authorized encoders and administrators only.</p>
            <form method="POST" action="{{ route('login.store') }}" class="grid">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>
                <label style="display: flex; gap: 8px; align-items: center;">
                    <input type="checkbox" name="remember" value="1" style="width: auto;"> Remember this device
                </label>
                <button class="btn" type="submit">Login</button>
            </form>
        </div>
    </div>
</x-layouts.app>
