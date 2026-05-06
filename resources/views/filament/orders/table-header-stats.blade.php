<div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4">

    <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 transition hover:shadow-md">
        <div class="flex items-center gap-x-4">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-500/10 p-3">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 2.25v.75m0 2.25v.75m0 2.25v.75m11.25-8.25v.75m0 2.25v.75m0 2.25v.75m0 2.25v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-6">Всего</p>
                <h3 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $total }}
                </h3>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-1 bg-blue-600"></div>
    </div>

    <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 transition hover:shadow-md">
        <div class="flex items-center gap-x-4">
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-500/10 p-3">
                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-6">Количество</p>
                <h3 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $count }}
                </h3>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-1 bg-emerald-600"></div>
    </div>

    <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 transition hover:shadow-md">
        <div class="flex items-center gap-x-4">
            <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 p-3">
                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-6">Среднее</p>
                <h3 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $avg }}
                </h3>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-1 bg-amber-600"></div>
    </div>

</div>