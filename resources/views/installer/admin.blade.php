@extends('installer::installer.layout')

@section('content')
<h2 class="text-xl font-semibold mb-4">Create Admin Account</h2>

<form action="{{ route('install.admin.store') }}" method="post" class="space-y-4">
    @csrf
    <input type="text" name="name" placeholder="Full Name" class="border p-2 w-full" required>
    <input type="email" name="email" placeholder="Email" class="border p-2 w-full" required>
    <input type="password" name="password" placeholder="Password" class="border p-2 w-full" required>
    <input type="password" name="password_confirmation" placeholder="Confirm Password" class="border p-2 w-full" required>
    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Finish</button>
</form>
@endsection
