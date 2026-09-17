<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <form wire:submit.prevent class="flex items-end gap-4 mb-6">
            <div>
                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-700 dark:text-gray-300">As of date</label>
                <input type="date" wire:model.live="asOfDate"
                    class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
            </div>
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                    <th class="py-2 pr-4">Code</th>
                    <th class="py-2 pr-4">Account</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4 text-right">Debit</th>
                    <th class="py-2 text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $row)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2 pr-4 font-mono">{{ $row['code'] }}</td>
                        <td class="py-2 pr-4">{{ $row['name'] }}</td>
                        <td class="py-2 pr-4 capitalize text-gray-500">{{ $row['account_type'] }}</td>
                        <td class="py-2 pr-4 text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                        <td class="py-2 text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400">No posted activity as of this date.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->rows->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-white/20 font-semibold">
                        <td colspan="3" class="py-2 pr-4 text-right">Total</td>
                        <td class="py-2 pr-4 text-right">{{ number_format($this->rows->sum('debit'), 2) }}</td>
                        <td class="py-2 text-right">{{ number_format($this->rows->sum('credit'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-filament-panels::page>
