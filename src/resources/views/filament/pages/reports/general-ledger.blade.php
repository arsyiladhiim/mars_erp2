<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <form wire:submit.prevent class="flex flex-wrap items-end gap-4 mb-6">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Account</label>
                <select wire:model.live="chartOfAccountId"
                    class="fi-input mt-1 block min-w-64 rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600">
                    @foreach ($this->accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
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

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Number</th>
                    <th class="py-2 pr-4">Description</th>
                    <th class="py-2 pr-4 text-right">Debit</th>
                    <th class="py-2 pr-4 text-right">Credit</th>
                    <th class="py-2 text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $row)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($row['entry_date'])->format('d M Y') }}</td>
                        <td class="py-2 pr-4 font-mono">{{ $row['number'] }}</td>
                        <td class="py-2 pr-4 text-gray-600 dark:text-gray-400">{{ $row['description'] ?: $row['memo'] }}</td>
                        <td class="py-2 pr-4 text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                        <td class="py-2 pr-4 text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                        <td class="py-2 text-right font-medium">{{ number_format($row['running_balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">No posted activity in this date range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
