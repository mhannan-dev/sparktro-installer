@extends('installer::installer.layout')
@section('content')
    <div class="py-4 text-center">
        <div class="mb-6">
            <div class="flex justify-center items-center mx-auto mb-4 w-20 h-20 bg-green-100 rounded-full">
                <i class="text-4xl text-green-500 fas fa-check"></i>
            </div>

            <h2 class="mb-2 text-2xl font-semibold text-gray-800">Installation Completed Successfully!</h2>
            <p class="mb-6 text-gray-600">{{ config('app.name') }} is now ready to use. You can now access your application.
            </p>

            <div class="p-4 mx-auto mb-6 max-w-md bg-green-50 rounded border border-green-200">
                <h4 class="flex justify-center items-center font-medium text-green-800">
                    <i class="mr-2 fas fa-rocket"></i> What's Next?
                </h4>
                <ul class="mt-2 text-sm text-left text-green-700">
                    <li class="flex items-start mb-2">
                        <i class="mt-1 mr-2 text-green-500 fas fa-check-circle"></i>
                        <span>Log in with your administrator account</span>
                    </li>
                    <li class="flex items-start mb-2">
                        <i class="mt-1 mr-2 text-green-500 fas fa-check-circle"></i>
                        <span>Configure your organization settings</span>
                    </li>
                    <li class="flex items-start">
                        <i class="mt-1 mr-2 text-green-500 fas fa-check-circle"></i>
                        <span>Add team members and set up workflows</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ $appUrl }}"
                class="inline-flex items-center px-6 py-3 text-lg font-medium text-white rounded btn-primary">
                Go to Application <i class="ml-2 fas fa-external-link-alt"></i>
            </a>
        </div>
        <div class="mt-6 text-sm text-gray-500">
            <p>Thank you for choosing {{ config('app.name') }}!</p>
        </div>
    </div>
@endsection
