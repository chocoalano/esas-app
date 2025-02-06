@php
    use App\Models\CoreApp\Departement;

    // Fungsi untuk mendapatkan inisial
    function getInitials(string $name): string
    {
        $words = explode(' ', $name);
        $initials = collect($words)
            ->map(fn($word) => strtoupper($word[0] ?? '')) // Ambil huruf pertama, default ke string kosong
            ->take(2) // Ambil maksimal 2 huruf
            ->implode(''); // Gabungkan menjadi satu string

        return $initials;
    }

    // Ambil nama user dan generate inisial
    $user = Auth::user();
    $name = $user->name ?? 'Unknown';
    $inisial = getInitials($name);

    // Format avatar inisial
    $avatar_initial = strlen($inisial) > 1 ? substr($inisial, 0, 1) . '+' . substr($inisial, 1, 1) : $inisial;

    // Generate URL untuk avatar
    $avatar_image =
        $user->avatar ? Storage::url($user->avatar) :  'https://ui-avatars.com/api/?name=' . urlencode($avatar_initial) . '&color=FFFFFF&background=171b33';

    // Ambil nama departemen jika ada
    $auth_departement = optional($user->employee)->departement_id
        ? Departement::find($user->employee->departement_id)->name ?? 'Unknown'
        : 'Unknown';
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 py-3">
        <!-- Avatar and User Info Section -->
        <div class="max-w col-span-1 sm:col-span-1 md:col-span-1 flex flex-col justify-between">
            <div class="fi-card-profile flex flex-col fi-contained rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                style="padding-top: 2rem;">
                <!-- Avatar -->
                <div class="relative mx-auto w-20 rounded-full mb-4">
                    <span
                        class="absolute right-0 m-3 h-3 w-3 rounded-full bg-green-500 ring-2 ring-green-300 ring-offset-2"></span>
                    <img class="mx-auto h-auto w-full rounded-full" src="{{ $avatar_image }}"
                        alt="{{ Auth::user()->avatar }}" />
                </div>
                <!-- User Info -->
                <h1 class="my-1 text-center text-xl font-bold leading-8">
                    {{ Str::title(Auth::user()->name) }}</h1>
                <h3 class="font-lg text-semibold text-center leading-6">
                    {{ Str::title($auth_departement ?? 'Unknown') }}. {{ Auth::user()->company->name }}
                </h3>
                <p class="text-center text-sm leading-6 text-gray-500 hover:text-gray-600">
                    {{ Str::limit(Auth::user()->address->residential_address ?? 'Not have an address', 50, '...') }}
                </p>
                <!-- Status List -->
                <ul class="mt-3 divide-y rounded py-2 px-3">
                    <li class="flex items-center py-3 text-sm justify-between">
                        <span>Status</span>
                        <span class="ml-auto">
                            <span
                                class="rounded-full bg-green-200 {{ Auth::user()->status === 'active' ? 'bg-green-200' : 'bg-red-200' }} py-1 px-2 text-xs font-medium {{ Auth::user()->status === 'active' ? 'text-green-700' : 'text-red-700' }}">
                                {{ Auth::user()->status ?? 'Unknown' }}
                            </span>
                        </span>
                    </li>
                    <li class="flex items-center py-3 text-sm justify-between">
                        <span>Joined On</span>
                        <span>{{ Auth::user()->employee->join_date ?? 'Unknown' }}</span>
                    </li>
                    <li class="flex items-center py-3 text-sm justify-between">
                        <span>Sign On</span>
                        <span>{{ Auth::user()->employee->sign_date ?? 'Unknown' }}</span>
                    </li>
                    <li class="flex items-center py-3 text-sm justify-between">
                        <span>Leave saldo</span>
                        <span>{{ Auth::user()->employee->saldo_cuti ?? 'Unknown' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Form Section -->
        <div class="col-span-1 sm:col-span-1 md:col-span-2">
            <x-filament-panels::form wire:submit="update">
                {{ $this->form }}
                <x-filament-panels::form.actions :actions="$this->getFormActions()" />
            </x-filament-panels::form>
        </div>
    </div>
</x-filament-panels::page>
