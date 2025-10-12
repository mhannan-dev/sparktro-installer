@extends('installer::installer.layout')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="text-center">🚀 Welcome to Application Installer</h2>
    </div>
    <div class="card-body">
        <p>This installer will guide you through setting up your application.</p>
        
        <div class="alert alert-info">
            <strong>Before you start:</strong>
            <ul class="mb-0 mt-2">
                <li>Database credentials ready</li>
                <li>Server meets requirements</li>
                <li>Write permissions to storage folders</li>
            </ul>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('install.requirements') }}" class="btn btn-primary btn-lg">
                Start Installation
            </a>
        </div>
    </div>
</div>
@endsection