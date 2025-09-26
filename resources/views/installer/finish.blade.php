@extends('installer::installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4">Installation Completed 🎉</h2>
<p>Your system is ready to use.</p>

<div class="mt-6">
    <a href="{{ $appUrl }}" class="bg-blue-600 text-white px-4 py-2 rounded">Go to Application</a>
</div>

<p class="text-sm text-gray-500 mt-4">For security, please delete the <code>/packages/installer</code> folder.</p>
@endsection
