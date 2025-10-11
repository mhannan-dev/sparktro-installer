@extends('installer::installer.layout')

@section('content')
    <h2 class="flex items-center mb-4 text-xl font-semibold text-gray-800">
        <i class="mr-2 text-blue-500 fas fa-server"></i> System Requirements
    </h2>

    <div class="mb-2 text-sm text-gray-600">
        Your server must meet the following requirements to run {{ config('app.name') }}.
    </div>

    <div class="mb-6 divide-y divide-gray-100">
        @foreach($requirements as $key => $status)
            <div class="flex justify-between items-center requirement-item">
                <div>
                    <span class="font-medium">{{ $key }}</span>
                </div>
                @if($status)
                    <span class="status-badge status-success">
                        <i class="mr-1 fas fa-check-circle"></i> OK
                    </span>
                @else
                    <span class="status-badge status-danger">
                        <i class="mr-1 fas fa-times-circle"></i> Missing
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    @if(in_array(false, $requirements))
        <div class="p-3 mt-4 mb-6 text-sm text-yellow-800 bg-yellow-50 rounded border border-yellow-200">
            <i class="mr-1 fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> One or more requirements are not met. Please resolve these issues before proceeding.
        </div>
    @else
        <div class="p-3 mt-4 mb-6 text-sm text-green-800 bg-green-50 rounded border border-green-200">
            <i class="mr-1 fas fa-check-circle"></i>
            <strong>Success:</strong> All system requirements are met.
        </div>
    @endif

    <h3 class="flex items-center mb-4 text-lg font-semibold text-gray-800">
        <i class="mr-2 text-blue-500 fas fa-database"></i> Database Configuration
    </h3>

    <form action="{{ route('install.database') }}" method="post" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="db_host" class="block mb-1 text-sm font-medium text-gray-700">Database Host</label>
                <input type="text" id="db_host" name="db_host" placeholder="DB Host"
                       class="p-3 w-full rounded form-input" value="127.0.0.1" required>
            </div>

            <div>
                <label for="db_port" class="block mb-1 text-sm font-medium text-gray-700">Database Port</label>
                <input type="text" id="db_port" name="db_port" placeholder="DB Port"
                       class="p-3 w-full rounded form-input" value="3306" required>
            </div>
        </div>

        <div>
            <label for="db_name" class="block mb-1 text-sm font-medium text-gray-700">Database Name</label>
            <input type="text" id="db_name" name="db_name" placeholder="Database Name"
                   class="p-3 w-full rounded form-input" value="relief_hub_pro" required>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="db_user" class="block mb-1 text-sm font-medium text-gray-700">Database User</label>
                <input type="text" id="db_user" name="db_user" placeholder="Database User"
                       class="p-3 w-full rounded form-input" value="root" required>
            </div>

            <div>
                <label for="db_pass" class="block mb-1 text-sm font-medium text-gray-700">Database Password</label>
                <input type="password" id="db_pass" name="db_pass" placeholder="Database Password"
                       class="p-3 w-full rounded form-input" value="11111111">
            </div>
        </div>

        <div class="flex justify-between items-center pt-4">
            <div></div> <!-- Empty div for alignment -->

            <button type="submit" class="flex items-center px-6 py-3 font-medium text-white rounded btn-primary"
                    @if(in_array(false, $requirements)) disabled @endif>
                Next Step <i class="ml-2 fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
@endsection
