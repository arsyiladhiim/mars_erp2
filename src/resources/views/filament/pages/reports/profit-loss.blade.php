<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <form wire:submit.prevent class="flex items-end gap-4 mb-6">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                <input type="date" wire:model.live="from"
                    class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                <input type="date" wire:model.live="to"
                    class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
            </div>
        </form>

        @php($f = $this->figures)

        <table class="w-full text-sm max-w-xl">
            <tbody>
                <tr class="border-b border-gray-100 dark:border-white/5">
                    <td class="py-2">Revenue</td>
                    <td class="py-2 text-right">{{ number_format($f['revenue'], 2) }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-white/5">
                    <td class="py-2">Cost of Goods Sold</td>
                    <td class="py-2 text-right">({{ number_format($f['cogs'], 2) }})</td>
                </tr>
                <tr class="border-b-2 border-gray-300 dark:border-white/20 font-semibold">
                    <td class="py-2">Gross Profit</td>
                    <td class="py-2 text-right">{{ number_format($f['gross_profit'], 2) }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-white/5">
                    <td class="py-2">Operating Expenses</td>
                    <td class="py-2 text-right">({{ number_format($f['expenses'], 2) }})</td>
                </tr>
                <tr class="border-t-2 border-gray-300 dark:border-white/20 font-bold text-base">
                    <td class="py-3">Net Profit</td>
                    <td class="py-3 text-right {{ $f['net_profit'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($f['net_profit'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
