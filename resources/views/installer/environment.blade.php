@extends('installer::installer.layout')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>⚙️ Environment Configuration</h2>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <h4>Application Settings</h4>
                    
                    <div class="mb-3">
                        <label class="form-label">App Name</label>
                        <input type="text" class="form-control" name="app_name" value="{{ old('app_name', 'My Application') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">App URL</label>
                        <input type="url" class="form-control" name="app_url" value="{{ old('app_url', url('/')) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4>Database Settings</h4>
                    
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Port</label>
                        <input type="text" class="form-control" name="db_port" value="{{ old('db_port', '3306') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_name" value="{{ old('db_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-control" name="db_user" value="{{ old('db_user') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_pass" value="{{ old('db_pass') }}">
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    Save & Continue
                </button>
            </div>
        </form>
    </div>
</div>
@endsection