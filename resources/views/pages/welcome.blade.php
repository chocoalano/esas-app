@extends('layouts.app')

@section('content')
<div class="bg-white p-6 md:p-8 rounded-lg shadow-2xl transform hover:scale-105 transition-transform duration-300 w-full max-w-4xl mx-auto flex flex-col justify-between md:flex-row md:items-center space-y-6 md:space-y-0 md:space-x-6">
    <!-- Keterangan Absen -->
    <div class="md:w-1/2 w-full">
        <h2 class="text-xl md:text-2xl font-semibold text-center md:text-left">Absen dengan Kode QR</h2>
        <p class="text-gray-700 mt-2 text-center md:text-left">
            Instruksi untuk melakukan absen.
        </p>
        <ol class="list-decimal list-inside text-gray-700 mt-4 space-y-2 text-sm md:text-base">
            <li>Buka smartphone Anda</li>
            <li>Tap <span class="font-bold">absen</span> pada Android, atau <span class="font-bold">absen</span> pada iPhone</li>
            <li>Pilih <span class="font-bold">Departemen</span>, <span class="font-bold">jam absen</span>, dan <span class="font-bold">waktu masuk/pulang</span></li>
            <li>Scan kode QR untuk melakukan absen <span class="font-bold">masuk/pulang</span></li>
        </ol>

        <!-- Form -->
        <form method="GET" id="myForm" class="flex flex-col mt-6 space-y-3">
            <div class="grid grid-cols-3 md:grid-cols-3 sm:grid-cols-3 gap-4">
                <!-- Departement Select -->
            <div class="w-full">
                <select name="departement_selected" onchange="submitForm()" class="w-full px-4 py-2 border rounded-md focus:ring focus:ring-green-300">
                    <option value="">Pilih departemen anda</option>
                    @foreach ($departement as $v)
                        <option value="{{ $v->id }}" {{ request()->query('departement_selected') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($timework)
            <!-- Timework Select -->
            <div class="w-full">
                <select name="timework_selected" onchange="submitForm()" class="w-full px-4 py-2 border rounded-md focus:ring focus:ring-green-300">
                    <option value="">Pilih jam kerja anda</option>
                    @foreach ($timework as $v)
                        <option value="{{ $v->id }}" {{ request()->query('timework_selected') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Presence Select -->
            <div class="w-full">
                <select name="type_presence" class="w-full px-4 py-2 border rounded-md focus:ring focus:ring-green-300">
                    <option value="in" {{ request()->query('type_presence') == 'in' ? 'selected' : '' }}>Masuk</option>
                    <option value="out" {{ request()->query('type_presence') == 'out' ? 'selected' : '' }}>Pulang</option>
                </select>
            </div>
            @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if (request()->query('departement_selected') && request()->query('timework_selected') && request()->query('type_presence'))
                <!-- Submit Button -->
                <button type="submit" class="w-full px-4 py-2 text-white bg-green-700 rounded-md hover:bg-green-600">
                    Generate QR
                </button>
                @endif

                <a href="{{ route('face-recognition') }}" class="w-full px-4 py-2 text-white bg-red-700 rounded-md hover:bg-red-600 text-center">
                    Tidak membawa Android / Pengguna iPhone
                </a>
            </div>

        </form>
    </div>

    <!-- QR Code -->
    <div class="md:w-1/3 md:h-1/3 p-6 flex flex-col md:items-center md:justify-center xl:ml-auto xl:mr-0">
        <div class="border p-4 md:p-6 rounded-lg bg-gray-100 flex flex-col items-center relative">
            @if ($qrCode)
                {!! $qrCode !!}
            @else
                <p class="text-gray-500 text-sm text-center">QR Code belum dibuat</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function submitForm() {
        document.getElementById('myForm').submit();
    }
</script>
@endsection
