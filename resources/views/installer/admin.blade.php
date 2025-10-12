@extends('installer::installer.layout')

@section('content')
    <h2 class="flex items-center mb-4 text-xl font-semibold text-gray-800">
        <i class="mr-2 text-blue-500 fas fa-user-shield"></i> Create Administrator Account
    </h2>

    <div class="mb-6 text-sm text-gray-600">
        Create the initial administrator account for accessing the {{ config('app.name') }} system.
    </div>

    <form action="{{ route('install.admin.store') }}" method="post" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block mb-1 text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Full Name"
                   class="p-3 w-full rounded form-input" required>
            <p class="mt-1 text-xs text-gray-500">Enter the administrator's full name</p>
        </div>

        <div>
            <label for="email" class="block mb-1 text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Email"
                   class="p-3 w-full rounded form-input" required>
            <p class="mt-1 text-xs text-gray-500">This will be used for login and notifications</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" placeholder="Password"
                       class="p-3 w-full rounded form-input" required>
            </div>

            <div>
                <label for="password_confirmation" class="block mb-1 text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password"
                       class="p-3 w-full rounded form-input" required>
            </div>
        </div>

        <div class="p-4 mt-4 bg-blue-50 rounded border border-blue-200">
            <h4 class="flex items-center font-medium text-blue-800">
                <i class="mr-2 fas fa-info-circle"></i> Security Recommendations
            </h4>
            <ul class="mt-2 text-sm list-disc list-inside text-blue-700">
                <li>Use a strong, unique password</li>
                <li>Include uppercase, lowercase, numbers, and symbols</li>
                <li>Avoid using personal information</li>
            </ul>
        </div>

        <div class="flex justify-between items-center pt-4">
            <a href="{{ url()->previous() }}" class="flex items-center font-medium text-blue-600 hover:text-blue-800">
                <i class="mr-2 fas fa-arrow-left"></i> Back
            </a>

            <button type="submit" class="flex items-center px-6 py-3 font-medium text-white rounded btn-primary">
                Create Account <i class="ml-2 fas fa-user-plus"></i>
            </button>
        </div>
    </form>
@endsection
