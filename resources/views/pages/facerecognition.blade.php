@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
        <div class="md:flex md:flex-row">
            <div class="md:w-1/2 p-6">
                <h2 class="text-xl md:text-2xl font-semibold">Absen Pendeteksi Wajah</h2>
                <p class="text-gray-700 mt-2 text-sm md:text-base">
                    Instruksi untuk melakukan absen.
                </p>
                <ol class="list-decimal list-inside text-gray-700 mt-4 space-y-2 text-sm md:text-base">
                    <li>Pilih <span class="font-bold">Departemen</span> dan <span class="font-bold">jam
                            absen</span> serta <span class="font-bold">waktu masuk/pulang</span></li>
                    <li>Isi NIP dengan benar</li>
                    <li>Hadapkan wajah ke kamera dengan ekspresi <span class="font-bold">tersenyum</span>
                        atau <span class="font-bold">gembira</span></li>
                    <li>Tingkat kemiripan minimal <span class="font-bold">0.40</span></li>
                </ol>

                <form method="GET" id="myForm" class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="departement-selected" class="block text-sm font-medium text-gray-700">Departemen</label>
                            <select name="departement_selected" onchange="submitForm()"
                                class="mt-1 w-full px-3 py-2 text-gray-700 bg-white border rounded-md shadow-sm focus:ring focus:ring-green-300"
                                id="departement-selected">
                                <option value="">Pilih departemen anda</option>
                                @foreach ($departement as $v)
                                <option value="{{ $v->id }}"
                                    {{ request()->query('departement_selected') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        @if ($timework)
                        <div>
                            <label for="timework-selected" class="block text-sm font-medium text-gray-700">Jam
                                Kerja</label>
                            <select name="timework_selected" onchange="submitForm()"
                                class="mt-1 w-full px-3 py-2 text-gray-700 bg-white border rounded-md shadow-sm focus:ring focus:ring-green-300"
                                id="timework-selected">
                                <option value="">Pilih jam kerja anda</option>
                                @foreach ($timework as $v)
                                <option value="{{ $v->id }}"
                                    {{ request()->query('timework_selected') == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="type-selected" class="block text-sm font-medium text-gray-700">Jenis</label>
                            <select name="type_presence"
                                class="mt-1 w-full px-3 py-2 text-gray-700 bg-white border rounded-md shadow-sm focus:ring focus:ring-green-300"
                                id="type-selected">
                                <option value="in"
                                    {{ request()->query('type_presence') == 'in' ? 'selected' : '' }}>Masuk
                                </option>
                                <option value="out"
                                    {{ request()->query('type_presence') == 'out' ? 'selected' : '' }}>Pulang
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                            <input type="number" name="nip"
                                class="mt-1 w-full px-3 py-2 text-gray-700 bg-white border rounded-md shadow-sm focus:ring focus:ring-green-300"
                                placeholder="Masukan NIP..." value="{{ request()->query('nip') }}" id="nip">
                        </div>

                        <div class="md:col-span-2 sm:col-span-2">  <button type="submit"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-green-700 rounded-md hover:bg-green-600 focus:ring focus:ring-green-300">
                                Mulai Pendeteksian Wajah
                            </button>
                        </div>

                        @endif
                        <div class="md:col-span-2 sm:col-span-2">  <a href="{{ route('index') }}"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-md hover:bg-blue-600 focus:ring focus:ring-blue-300 flex items-center justify-center">
                                Kembali absen Kode QR
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            @php
            $isFormCompleted = request()->query('departement_selected') && request()->query('timework_selected') &&
            request()->query('type_presence') && request()->query('nip');
            @endphp

            <div class="md:w-1/2 md:h-1/2 p-6 flex flex-col items-center justify-center">
                <h2 class="text-xl md:text-2xl font-semibold text-center mb-4">Face Recognition</h2>
                <div class="border p-4 md:p-6 rounded-lg bg-gray-100 flex flex-col items-center relative">
                    @if ($isFormCompleted)
                    <video id="video" autoplay playsinline class="rounded-md w-full"></video>
					<canvas id="canvas" class="absolute top-0 left-0 w-full h-full"></canvas>
                    <p id="status" class="mt-4 text-sm md:text-lg text-gray-700 font-medium">Mendeteksi wajah...</p>
                    @else
                    <p id="status" class="mt-4 text-sm md:text-lg text-gray-700 font-medium text-center">
                        Lengkapi form input terlebih dulu!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    function submitForm() {
        document.getElementById('myForm').submit();
    }
	const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const maxWidth = 500; // Contoh: lebar maksimum 500px
    const maxHeight = 500; // Contoh: tinggi maksimum 500px

    function resizeVideoCanvas() {
      let width = video.videoWidth;
      let height = video.videoHeight;

      if (width > maxWidth) {
        height = height * (maxWidth / width);
        width = maxWidth;
      }

      if (height > maxHeight) {
        width = width * (maxHeight / height);
        height = maxHeight;
      }
      video.style.maxWidth = `${width}px`;
      video.style.maxHeight = `${height}px`;
      canvas.style.maxWidth = `${width}px`;
      canvas.style.maxHeight = `${height}px`;
        canvas.width = width;
        canvas.height = height;
    }

    video.addEventListener('loadeddata', resizeVideoCanvas); // Saat data video dimuat
    window.addEventListener('resize', resizeVideoCanvas); // Saat ukuran window diubah
</script>
@endsection
