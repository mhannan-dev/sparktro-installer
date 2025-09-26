@extends('installer::installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4">System Requirements</h2>
<ul class="mb-6">
    @foreach($requirements as $key => $status)
        <li class="mb-2">
            {{ $key }} :
            @if($status)
                <span class="text-green-600">✅ OK</span>
            @else
                <span class="text-red-600">❌ Missing</span>
            @endif
        </li>
    @endforeach
</ul>

<form action="{{ route('install.database') }}" method="post" class="space-y-4">
    @csrf
    <h3 class="text-lg font-semibold">Database Configuration</h3>
    <input type="text" name="db_host" placeholder="DB Host" class="border p-2 w-full" required>
    <input type="text" name="db_port" placeholder="DB Port" value="3306" class="border p-2 w-full" required>
    <input type="text" name="db_name" placeholder="Database Name" class="border p-2 w-full" required>
    <input type="text" name="db_user" placeholder="Database User" class="border p-2 w-full" required>
    <input type="password" name="db_pass" placeholder="Database Password" class="border p-2 w-full">
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Next</button>
</form>
@endsection
