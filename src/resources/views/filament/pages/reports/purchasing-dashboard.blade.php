<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="font-semibold mb-3">Outstanding Purchase Orders</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Number</th>
                        <th class="py-1.5 pr-2">Supplier</th>
                        <th class="py-1.5 pr-2">Status</th>
                        <th class="py-1.5 text-right">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->outstandingPurchaseOrders as $po)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2 font-mono">{{ $po['number'] }}</td>
                            <td class="py-1.5 pr-2">{{ $po['supplier'] }}</td>
                            <td class="py-1.5 pr-2 text-gray-500">{{ $po['status'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($po['grand_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-gray-400">Nothing outstanding.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex items-end gap-4 mb-3">
                <h3 class="font-semibold">Supplier Spend</h3>
            </div>
            <form wire:submit.prevent class="flex items-end gap-4 mb-4">
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">From</label>
                    <input type="date" wire:model.live="from" class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-700 dark:text-gray-300">To</label>
                    <input type="date" wire:model.live="to" class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
                </div>
            </form>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Supplier</th>
                        <th class="py-1.5 pr-2 text-right">Orders</th>
                        <th class="py-1.5 text-right">Total Spend</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->supplierSpend as $row)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2">{{ $row['supplier'] }}</td>
                            <td class="py-1.5 pr-2 text-right">{{ $row['order_count'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($row['total_spend'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">No purchases in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
