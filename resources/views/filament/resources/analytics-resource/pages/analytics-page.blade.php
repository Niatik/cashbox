<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Date Range Filter --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            {{ $this->form }}
        </div>

        {{-- Financial Summary Section --}}
        @php
            $financial = $this->getFinancialSummary();
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 mr-2 text-green-600" />
                    {{ __('analytics.financial_summary') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('analytics.financial_summary_description') }}
                    @if(isset($this->data['price_id']) && $this->data['price_id'])
                        {{ __('messages.for_selected_service') }}
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.income') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.profit') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.expenses') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.cash_income') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.cashless_income') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                {{ number_format($financial['income'], 2, '.', ' ') }} ₸
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $financial['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($financial['profit'], 2, '.', ' ') }} ₸
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                {{ number_format($financial['expenses'], 2, '.', ' ') }} ₸
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                {{ number_format($financial['cash_income'], 2, '.', ' ') }} ₸
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">
                                {{ number_format($financial['cashless_income'], 2, '.', ' ') }} ₸
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Social Media Analytics Section --}}
        @php
            $socialMedia = $this->getSocialMediaAnalytics();
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-megaphone class="w-5 h-5 mr-2 text-blue-600" />
                    {{ __('analytics.social_media_effectiveness') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('analytics.social_media_description') }}
                    @if(isset($this->data['price_id']) && $this->data['price_id'])
                        {{ __('messages.for_selected_service') }}
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.social_media_name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.orders_count') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.people_count') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.total_sum') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($socialMedia as $index => $item)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                                    {{ strtoupper(substr($item['name'], 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $item['name'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($item['orders_count']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($item['total_people']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($item['total_sum'], 0, '.', ' ') }} ₸
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center py-8">
                                        <x-heroicon-o-chart-bar class="w-8 h-8 text-gray-400 mb-2" />
                                        <p class="text-sm">{{ __('messages.no_social_media_data') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(count($socialMedia) > 0)
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('analytics.total_orders', ['count' => number_format(collect($socialMedia)->sum('orders_count'))]) }}
                        </span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('analytics.total_people', ['count' => number_format(collect($socialMedia)->sum('total_people'))]) }}
                        </span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('analytics.total_revenue') }} {{ number_format(collect($socialMedia)->sum('total_sum'), 0, '.', ' ') }} ₸
                        </span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Expense Analytics Section --}}
        @php
            $expenses = $this->getExpenseAnalytics();
        @endphp

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <x-heroicon-o-minus-circle class="w-5 h-5 mr-2 text-red-600" />
                    {{ __('analytics.expense_analysis') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('analytics.expense_analysis_description') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.expense_type') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('analytics.total_amount') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($expenses as $index => $expense)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            @if($expense['type'] === __('analytics.salary_type'))
                                                <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <x-heroicon-o-user-group class="w-4 h-4 text-blue-600" />
                                                </div>
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                                                    <x-heroicon-o-banknotes class="w-4 h-4 text-red-600" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $expense['type'] }}
                                            </div>
                                            @if($expense['type'] === __('analytics.salary_type'))
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('analytics.employee_salaries') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-red-600">
                                        {{ number_format($expense['total_amount'], 2, '.', ' ') }} ₸
                                    </div>
                                    @php
                                        $totalExpenses = collect($expenses)->sum('total_amount');
                                        $percentage = $totalExpenses > 0 ? ($expense['total_amount'] / $totalExpenses) * 100 : 0;
                                    @endphp
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('analytics.percent_of_total', ['percent' => number_format($percentage, 1)]) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center py-8">
                                        <x-heroicon-o-banknotes class="w-8 h-8 text-gray-400 mb-2" />
                                        <p class="text-sm">{{ __('messages.no_expense_data') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(count($expenses) > 0)
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('analytics.total_categories', ['count' => count($expenses)]) }}
                        </span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('analytics.total_expenses') }} {{ number_format(collect($expenses)->sum('total_amount'), 2, '.', ' ') }} ₸
                        </span>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
