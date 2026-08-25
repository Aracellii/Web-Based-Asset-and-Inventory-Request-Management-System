@if((request()->routeIs('filament.admin.auth.login')) || (request()->routeIs('filament.admin.auth.register')))
<div class="flex flex-col items-center gap-4 text-center pb-4">
    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-600 text-xl font-bold text-white shadow-lg">
        SI
    </div>
    <div class="leading-tight">
        <div class="text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">
            SIATK
        </div>
        <div class="text-xs font-medium tracking-wider text-gray-500 uppercase px-4">
            Asset and Inventory Management System
        </div>
    </div>
</div>

@elseif(str_contains(request()->url(), 'password-reset') || str_contains(request()->headers->get('referer'), 'password-reset'))
<!-- No logo on password reset pages -->

@else
<div class="flex items-center gap-3">
    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-600 text-sm font-bold text-white">
        SI
    </div>
    <div class="leading-tight">
        <div class="text-xl font-bold">SIATK</div>
        <div class="text-xs font-medium tracking-wider text-white-500">
            Asset and Inventory Management System
        </div>
    </div>
</div>


@endif