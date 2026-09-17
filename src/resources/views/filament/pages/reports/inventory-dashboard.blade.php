<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Stock Value</div>
            <div class="text-2xl font-semibold mt-1">Rp {{ number_format($this->totalStockValue, 0, ',', '.') }}</div>
        </div>
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="text-sm text-gray-500 dark:text-gray-400">Low Stock Items</div>
            <div class="text-2xl font-semibold mt-1 {{ $this->lowStockItems->isNotEmpty() ? 'text-danger-600' : '' }}">
                {{ $this->lowStockItems->count() }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="font-semibold mb-3">Low Stock</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Item</th>
                        <th class="py-1.5 pr-2 text-right">On Hand</th>
                        <th class="py-1.5 text-right">Reorder Point</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->lowStockItems as $item)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2">{{ $item['sku'] }} — {{ $item['name'] }}</td>
                            <td class="py-1.5 pr-2 text-right text-danger-600">{{ number_format($item['on_hand'], 2) }}</td>
                            <td class="py-1.5 text-right">{{ number_format($item['reorder_point'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">Nothing below its reorder point.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <h3 class="font-semibold mb-3">Recent Movement</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                        <th class="py-1.5 pr-2">Date</th>
                        <th class="py-1.5 pr-2">Item</th>
                        <th class="py-1.5 pr-2">Warehouse</th>
                        <th class="py-1.5 pr-2">Type</th>
                        <th class="py-1.5 text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recentMovements as $movement)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-1.5 pr-2">{{ \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d M') }}</td>
                            <td class="py-1.5 pr-2">{{ $movement->sku }}</td>
                            <td class="py-1.5 pr-2">{{ $movement->warehouse_name }}</td>
                            <td class="py-1.5 pr-2 text-gray-500">{{ $movement->movement_type }}</td>
                            <td class="py-1.5 text-right {{ $movement->quantity_in > 0 ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $movement->quantity_in > 0 ? '+'.number_format($movement->quantity_in, 2) : '-'.number_format($movement->quantity_out, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-400">No movement yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
