<div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4">

    <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-1 transition hover:shadow-md">
        <div class="flex items-center gap-x-4">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 p-3">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 2.25v.75m0 2.25v.75m0 2.25v.75m11.25-8.25v.75m0 2.25v.75m0 2.25v.75m0 2.25v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-6">Всего</p>
                <p class="text-sm font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $total }}
                </p>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-1 bg-blue-600"></div>
    </div>

    <x-filament-actions::actions
        :actions="$this->getCachedHeaderActions()"
        alignment="right"
    />

</div>