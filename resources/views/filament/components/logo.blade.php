@if((request()->routeIs('filament.admin.auth.login')) || (request()->routeIs('filament.admin.auth.register')))
<div class="flex flex-col items-center gap-4 text-center pb-3">
    <img
        src="{{ asset('build/assets/logo.svg') }}"
        alt="SIATK"
        class="w-20 h-20 object-contain">
    <div class="leading-tight">
        <div class="text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">
            Sistem Informasi Aset & Tata Kelola
        </div>
        <div class="text-xs font-medium tracking-wider text-gray-500 uppercase px-4">
            SIATK
        </div>
    </div>
</div>

@elseif(str_contains(request()->url(), 'password-reset') || str_contains(request()->headers->get('referer'), 'password-reset'))
<!-- Tidak usah ada logo untuk forgot password -->

@else
<div class="flex items-center gap-3">
    <img
        src="{{ asset('build/assets/logo.svg') }}"
        alt="SIATK"
        class="w-9 h-9 object-contain">
    <div class="leading-tight">
        <div class="text-xl font-bold">SIATK</div>
        <div class="text-xs font-medium tracking-wider text-white-500">
            Sistem Informasi Aset & Tata Kelola
        </div>
    </div>
</div>

@endif