<?php

namespace App\Console\Commands;

use App\Models\Core\Company;
use App\Services\Asset\AssetDepreciationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use RuntimeException;

class DepreciateAssetsCommand extends Command
{
    protected $signature = 'assets:depreciate {--date= : Any date within the period to depreciate; defaults to today}';

    protected $description = 'Posts monthly straight-line depreciation for every company as of the given period.';

    public function handle(AssetDepreciationService $service): int
    {
        $periodDate = ($date = $this->option('date')) ? Carbon::parse($date) : now();

        foreach (Company::all() as $company) {
            try {
                $journalEntry = $service->runForCompany($company->id, $periodDate);

                if ($journalEntry) {
                    $this->info("{$company->name}: posted {$journalEntry->number} for Rp".number_format((float) $journalEntry->total_debit, 2));
                } else {
                    $this->line("{$company->name}: no depreciation due for {$periodDate->format('F Y')}.");
                }
            } catch (RuntimeException $e) {
                $this->error("{$company->name}: skipped — {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
