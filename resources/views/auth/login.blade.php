<x-layouts.auth :title="__('Login')">
    <!-- Login Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Login</h1>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <!-- NIK Input -->
                <div class="mb-4">
                    <x-forms.input label="NIK" name="nik" type="nik" />
                </div>

                <!-- Password Input -->
                <div class="mb-4">
                    <x-forms.input label="Password" name="password" type="password" />
                </div>

                <!-- Remember Me -->
                <div class="mb-6">
                    <x-forms.checkbox label="Remember me" name="remember" />
                </div>

                <!-- Login Button -->
                <x-button type="primary" class="w-full">Login</x-button>
            </form>

            @if (Route::has('register'))
                <!-- Register Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                            class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Register</a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.auth>
