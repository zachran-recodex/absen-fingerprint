<x-layouts.auth>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Register</h1>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <!-- Full Name Input -->
                <div class="mb-4">
                    <x-forms.input label="Nama Lengkap" name="name" type="text" />
                </div>

                <!-- NIK Input -->
                <div class="mb-4">
                    <x-forms.input label="NIK" name="nik" type="nik" />
                </div>

                <!-- Password Input -->
                <div class="mb-4">
                    <x-forms.input label="Password" name="password" type="password"  />
                </div>

                <!-- Confirm Password Input -->
                <div class="mb-4">
                    <x-forms.input label="Konfirmasi Password" name="password_confirmation" type="password" />
                </div>

                <!-- Register Button -->
                <x-button type="primary" class="w-full">Register</x-button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Sudah punya akun?
                    <a href="{{ route('login') }}"
                        class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Login</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.auth>
