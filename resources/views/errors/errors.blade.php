@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center bg-gray-100 px-6">
    <div class="max-w-lg text-center p-8">
        <h1 class="text-8xl font-extrabold text-green-700">{{ $status }}</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mt-4">Oops! Error occurred</h2>
        <p class="text-gray-600 mt-2">{{ $msg }}</p>

        <a href="{{ route('filament.app.auth.login') }}"
           class="mt-6 inline-block px-6 py-3 text-white bg-green-600 rounded-md shadow-md hover:bg-green-500 transition duration-300">
            Back to login
        </a>
    </div>
</div>
@endsection
