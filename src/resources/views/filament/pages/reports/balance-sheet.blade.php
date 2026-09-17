<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <form wire:submit.prevent class="flex items-end gap-4 mb-6">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">As of date</label>
                <input type="date" wire:model.live="asOfDate"
                    class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
            </div>
        </form>

        @php($f = $this->figures)

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="font-semibold mb-2">Assets</h3>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($f['accounts']->where('account_type', 'asset') as $account)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-1.5">{{ $account['code'] }} — {{ $account['name'] }}</td>
                                <td class="py-1.5 text-right">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-t-2 border-gray-300 dark:border-white/20 font-semibold">
                            <td class="py-2">Total Assets</td>
                            <td class="py-2 text-right">{{ number_format($f['total_assets'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Liabilities</h3>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($f['accounts']->where('account_type', 'liability') as $account)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-1.5">{{ $account['code'] }} — {{ $account['name'] }}</td>
                                <td class="py-1.5 text-right">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-t border-gray-300 dark:border-white/20 font-semibold">
                            <td class="py-2">Total Liabilities</td>
                            <td class="py-2 text-right">{{ number_format($f['total_liabilities'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <h3 class="font-semibold mb-2 mt-6">Equity</h3>
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($f['accounts']->where('account_type', 'equity') as $account)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-1.5">{{ $account['code'] }} — {{ $account['name'] }}</td>
                                <td class="py-1.5 text-right">{{ number_format($account['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-t border-gray-300 dark:border-white/20 font-semibold">
                            <td class="py-2">Total Equity</td>
                            <td class="py-2 text-right">{{ number_format($f['total_equity'], 2) }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-300 dark:border-white/20 font-bold">
                            <td class="py-2">Total Liabilities &amp; Equity</td>
                            <td class="py-2 text-right">{{ number_format($f['total_liabilities_and_equity'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
