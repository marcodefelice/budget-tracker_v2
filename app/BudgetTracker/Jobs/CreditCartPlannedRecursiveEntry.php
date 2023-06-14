<?php

namespace App\BudgetTracker\Jobs;

use App\BudgetTracker\Entity\Entries\Entry as EntriesEntry;
use App\BudgetTracker\Enums\EntryType;
use App\BudgetTracker\Models\Account;
use App\BudgetTracker\Models\Currency;
use App\BudgetTracker\Models\PaymentsTypes;
use App\BudgetTracker\Services\ExpensesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\BudgetTracker\Models\Entry;
use App\BudgetTracker\Models\SubCategory;
use App\BudgetTracker\Services\EntryService;
use Illuminate\Support\Facades\Log;

class CreditCartPlannedRecursiveEntry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->handle();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Find credit card entry to insert");
        try {

            $accounts = $this->findCreditCards();
            foreach($accounts as $account) {
                Log::debug("Found account ".$account->id);
                $entry = $this->entry($account->installementValue,$account);

                if($this->exist($entry->getNote()) === false) {
                    $service = new EntryService();
                    $entryArray = $entry->toArray();
                    $entryArray['user_id'] = $account->user_id;

                    $type = EntryType::Incoming;
                    if($entry->getAmount() < 0) {
                        $type = EntryType::Expenses;
                    }

                    $service->save($entryArray,$type);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error while insert new credit card recursive entry '.$e->getMessage());
        }
    }

    /**
     * find planned entries
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function findCreditCards() : \Illuminate\Database\Eloquent\Collection
    {   
        return Account::where('installement',1)->get();
    }

    private function entry(float $amount,Account $account): EntriesEntry
    {
        $date = new \DateTime($account->date);
        $day = $date->format('d');
        $newDate = date('Y',time()).'-'.date('m',time()).'-'.$day;

        return new EntriesEntry(
            $amount,
            Currency::find(11),
            $account->name . ' '. $newDate,
            SubCategory::find(55),
            $account,
            PaymentsTypes::find(1),
            new \DateTime($newDate),
        );
    }

    private function exist($note): bool
    {
        $entry = Entry::where('note', $note)->get();
        if($entry->count() == 0) {
            Log::debug("No entry found with ".$note);
            return false;
        }
        Log::debug("Founded entry ".$note);
        return true;
    }

    private function getInterests(\Illuminate\Database\Eloquent\Collection $account): float
    {
        $interests = $account->interests;
    }
}
