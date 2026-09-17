<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <form wire:submit.prevent class="flex items-end gap-4 mb-6">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">As of date</label>
                <input type="date" wire:model.live="asOfDate"
                    class="fi-input mt-1 block rounded-lg border-gray-300 shadow-sm text-sm dark:bg-gray-800 dark:border-gray-600" />
            </div>
        </form>

        @php
            $buckets = ['Current', '1-30', '31-60', '61-90', '90+'];
            $bucketTotals = $this->rows->groupBy('bucket')->map(fn ($group) => $group->sum('outstanding'));
        @endphp

        <div class="grid grid-cols-5 gap-3 mb-6">
            @foreach ($buckets as $bucket)
                <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 text-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $bucket }}</div>
                    <div class="text-lg font-semibold {{ $bucket === '90+' ? 'text-danger-600' : ($bucket === 'Current' ? 'text-success-600' : 'text-warning-600') }}">
                        {{ number_format($bucketTotals[$bucket] ?? 0, 0) }}
                    </div>
                </div>
            @endforeach
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 text-left text-gray-500 dark:text-gray-400">
                    <th class="py-2 pr-4">{{ $this->partnerLabel }}</th>
                    <th class="py-2 pr-4">Invoice #</th>
                    <th class="py-2 pr-4">Due Date</th>
                    <th class="py-2 pr-4 text-right">Days Overdue</th>
                    <th class="py-2 pr-4">Bucket</th>
                    <th class="py-2 text-right">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $row)
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <td class="py-2 pr-4">{{ $row['business_partner'] }}</td>
                        <td class="py-2 pr-4 font-mono">{{ $row['number'] }}</td>
                        <td class="py-2 pr-4">{{ $row['due_date'] ? \Illuminate\Support\Carbon::parse($row['due_date'])->format('d M Y') : '—' }}</td>
                        <td class="py-2 pr-4 text-right">{{ $row['days_overdue'] > 0 ? $row['days_overdue'] : '—' }}</td>
                        <td class="py-2 pr-4">
                            <span @class([
                                'fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                                'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' => $row['bucket'] === 'Current',
                                'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' => in_array($row['bucket'], ['1-30', '31-60', '61-90']),
                                'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' => $row['bucket'] === '90+',
                            ])>{{ $row['bucket'] }}</span>
                        </td>
                        <td class="py-2 text-right font-medium">{{ number_format($row['outstanding'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">No outstanding invoices as of this date.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($this->rows->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-white/20 font-semibold">
                        <td colspan="5" class="py-2 pr-4 text-right">Total Outstanding</td>
                        <td class="py-2 text-right">{{ number_format($this->rows->sum('outstanding'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-filament-panels::page>
