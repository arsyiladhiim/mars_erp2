<x-filament-panels::page>
    <form wire:submit.prevent class="flex items-end gap-4 mb-6">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
            <input type="date" wire:model.live="from" class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
            <input type="date" wire:model.live="to" class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Sales</div>
            <div class="text-2xl font-semibold mt-1">Rp {{ number_format($this->summary['total_sales'], 0, ',', '.') }}</div>
        </div>
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Invoices</div>
            <div class="text-2xl font-semibold mt-1">{{ $this->summary['invoice_count'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="font-semibold mb-3">Top Products</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Item</th>
                        <th class="py-1.5 pr-2 text-right">Qty Sold</th>
                        <th class="py-1.5 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->topProducts as $product)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2">{{ $product['sku'] }} — {{ $product['name'] }}</td>
                            <td class="py-1.5 pr-2 text-right">{{ number_format($product['quantity_sold'], 2) }}</td>
                            <td class="py-1.5 text-right">{{ number_format($product['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">No sales in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="font-semibold mb-3">Top Customers</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Customer</th>
                        <th class="py-1.5 pr-2 text-right">Invoices</th>
                        <th class="py-1.5 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->topCustomers as $customer)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2">{{ $customer['customer'] }}</td>
                            <td class="py-1.5 pr-2 text-right">{{ $customer['invoice_count'] }}</td>
                            <td class="py-1.5 text-right">{{ number_format($customer['total_revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">No sales in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
