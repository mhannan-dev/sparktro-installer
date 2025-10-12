@extends('installer::installer.layout')

@section('content')
    <h2 class="flex items-center mb-4 text-xl font-semibold text-gray-800">
        <i class="mr-2 text-blue-500 fas fa-server"></i> System Requirements
    </h2>

    <p class="mb-2 text-sm text-gray-600">
        Your server must meet the following requirements to run <strong>{{ config('app.name') }}</strong>.
    </p>

    <div class="mb-6 divide-y divide-gray-100">
        @foreach($requirements as $key => $status)
            <div class="flex justify-between items-center py-2">
                <span class="font-medium">{{ $key }}</span>
                @if($status)
                    <span class="text-green-600 flex items-center"><i class="mr-1 fas fa-check-circle"></i> OK</span>
                @else
                    <span class="text-red-600 flex items-center"><i class="mr-1 fas fa-times-circle"></i> Missing</span>
                @endif
            </div>
        @endforeach
    </div>

    @if(in_array(false, $requirements))
        <div class="p-3 mt-4 mb-6 text-sm text-yellow-800 bg-yellow-50 rounded border border-yellow-200 flex items-center">
            <i class="mr-2 fas fa-exclamation-triangle"></i>
            <span>One or more requirements are not met. Please resolve these issues before proceeding.</span>
        </div>
    @else
        <div class="p-3 mt-4 mb-6 text-sm text-green-800 bg-green-50 rounded border border-green-200 flex items-center">
            <i class="mr-2 fas fa-check-circle"></i>
            <span>All system requirements are met.</span>
        </div>
    @endif

    <h3 class="flex items-center mb-4 text-lg font-semibold text-gray-800">
        <i class="mr-2 text-blue-500 fas fa-database"></i> Database Configuration
    </h3>

    <form action="{{ route('install.database') }}" method="post" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="application_url" class="block mb-1 text-sm font-medium text-gray-700">Application URL</label>
                <input type="text" id="application_url" name="application_url"
                       class="p-3 w-full rounded border border-gray-300"
                       value="{{ rtrim(str_replace('/public', '', request()->getSchemeAndHttpHost()), '/') }}"
                       readonly required>
            </div>

            <div>
                <label for="db_host" class="block mb-1 text-sm font-medium text-gray-700">Database Host</label>
                <input type="text" id="db_host" name="db_host"
                       class="p-3 w-full rounded border border-gray-300"
                       value="127.0.0.1" required>
            </div>
        </div>

        <!-- Database Port & Name on same row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="db_port" class="block mb-1 text-sm font-medium text-gray-700">Database Port</label>
                <input type="text" id="db_port" name="db_port"
                       class="p-3 w-full rounded border border-gray-300"
                       value="3306" required>
            </div>

            <div>
                <label for="db_name" class="block mb-1 text-sm font-medium text-gray-700">Database Name</label>
                <input type="text" id="db_name" name="db_name"
                       class="p-3 w-full rounded border border-gray-300"
                       value="relief_hub_pro" required>
            </div>
        </div>

        <!-- Database User & Password on same row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="db_user" class="block mb-1 text-sm font-medium text-gray-700">Database User</label>
                <input type="text" id="db_user" name="db_user"
                       class="p-3 w-full rounded border border-gray-300"
                       value="root" required>
            </div>

            <div>
                <label for="db_pass" class="block mb-1 text-sm font-medium text-gray-700">Database Password</label>
                <input type="password" id="db_pass" name="db_pass"
                       class="p-3 w-full rounded border border-gray-300"
                       value="11111111">
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit"
                    class="flex items-center px-6 py-3 font-medium text-white rounded bg-blue-600 hover:bg-blue-700"
                    @if(in_array(false, $requirements)) disabled @endif>
                Next Step <i class="ml-2 fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
@endsection
