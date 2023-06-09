<?php

namespace App\Stats\Services;

use App\BudgetTracker\Models\Incoming;
use App\BudgetTracker\Models\Entry;
use App\BudgetTracker\Entity\Wallet;
use App\BudgetTracker\Enums\EntryType;
use App\BudgetTracker\Models\Account;
use App\BudgetTracker\Models\Debit;
use App\BudgetTracker\Models\Expenses;
use App\BudgetTracker\Models\Transfer;
use DateTime;

class StatsService
{

    private string $startDate;
    private string $endDate;
    private string $startDatePassed;
    private string $endDatePassed;

    public function __construct(string $startDate, string $endDate)
    {
        $this->setDateEnd($endDate);
        $this->setDateStart($startDate);
    }

    /**
     * set data to start stats
     * @param string $date
     * 
     * @return void
     */
    private function setDateStart(string $date): void
    {
        $this->startDate = $date;

        $passedDateTime = new DateTime($date);
        $passedDateTime = $passedDateTime->modify('-1 month');
        $this->startDatePassed = $passedDateTime->format('Y-m-d H:i:s');

    }

    /**
     * set data to start stats
     * @param string $date
     * 
     * @return self
     */
    private function setDateEnd(string $date): void
    {
        $this->endDate = $date;

        $passedDateTime = new DateTime($date);
        $passedDateTime = $passedDateTime->modify('-1 month');
        $this->endDatePassed = $passedDateTime->format('Y-m-d H:i:s');

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return array
     */
    public function incoming(bool $planning): Array
    {
        $entry = Incoming::user();
        $entry->where('date_time', '<=', $this->endDate)
        ->where('date_time', '>=', $this->startDate)->where('type',EntryType::Incoming->value);

        $entryOld = Incoming::user();
        $entryOld->where('date_time', '<=', $this->endDatePassed)
        ->where('date_time', '>=', $this->startDatePassed)->where('type',EntryType::Incoming->value);

        if ($planning === true) {
            $entry->whereIn('planned',[0,1]);
            $entryOld->whereIn('planned',[0,1]);
        } else {
            $entry->where('planned',0);
            $entryOld->where('planned',0);
        }

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return array
     */
    public function expenses(bool $planning): array
    {
        $entry = Expenses::user();
        $entry->where('date_time', '<=', $this->endDate)
        ->where('date_time', '>=', $this->startDate)->where('type',EntryType::Expenses->value);

        $entryOld = Expenses::user();
        $entryOld->where('date_time', '<=', $this->endDatePassed)
        ->where('date_time', '>=', $this->startDatePassed)->where('type',EntryType::Expenses->value);

        if ($planning === true) {
            $entry->whereIn('planned',[0,1]);
            $entryOld->whereIn('planned',[0,1]);
        } else {
            $entry->where('planned',0);
            $entryOld->where('planned',0);
        }

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return array
     */
    public function transfer(bool $planning): array
    {
        $entry = Transfer::user();
        $entry->where('date_time', '<=', $this->endDate)
        ->where('date_time', '>=', $this->startDate)->where('type',EntryType::Transfer->value);

        $entryOld = Transfer::user();
        $entryOld->where('date_time', '<=', $this->endDatePassed)
        ->where('date_time', '>=', $this->startDatePassed)->where('type',EntryType::Transfer->value);

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return array
     */
    public function debit(bool $planning): array
    {
        $entry = Debit::user();
        $entry->where('date_time', '<=', $this->endDate)
        ->where('date_time', '>=', $this->startDate)->where('type',EntryType::Debit->value);

        $entryOld = Debit::user();
        $entryOld->where('date_time', '<=', $this->endDatePassed)
        ->where('date_time', '>=', $this->startDatePassed)->where('type',EntryType::Debit->value);

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];
    }

    /**
     * retrive total wallet sum
     * @param bool $planning
     * 
     * @return float
     */
    public function total(bool $planning): float
    {
        $wallet = new Wallet(0);

        $accounts = Account::user()->where('installement',0)->get();
        foreach($accounts as $account) {
            $wallet->deposit($account->balance);
        }

        if ($planning === true) {
            $plannedEntries = $this->getPlannedEntry();
            $wallet->sum($plannedEntries->toArray());
        }

        return $wallet->getBalance();
    }

    /**
     * get only planned entry
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPlannedEntry(): \Illuminate\Database\Eloquent\Collection
    {
        $dateTime = new DateTime('now');
        $dateTime = $dateTime->modify('Last day of this month');
        $dateTime = $dateTime->format('Y-m-d H:i:s');

        $dateTimeFirst = new DateTime('now');
        $dateTimeFirst = $dateTimeFirst->modify('First day of this month');
        $dateTimeFirst = $dateTimeFirst->format('Y-m-d H:i:s');

        $entry = Entry::leftJoin('accounts', 'accounts.id', '=', 'entries.account_id')
            ->where('entries.planned', 1)
            ->where('entries.date_time', '<=', $dateTime)
            ->where('entries.date_time', '>=', $dateTimeFirst)
            ->where('accounts.installement', 0)
            ->get('entries.amount');

        return $entry;
    }

    /** 
     * retrive all accounts
     * @param bool $planning
     * @param \Illuminate\Database\Eloquent\Collection $accounts
     * 
     * @return array
     */
    public function wallets(\Illuminate\Database\Eloquent\Collection $accounts): array
    {
        $response = [];
        foreach ($accounts as $account) {

            $wallet = new Wallet($account->balance);

            $response[] = [
                'account_id' => $account->id,
                'account_label' => $account->name,
                'color' => $account->color,
                'total_wallet' => $wallet->getBalance()
            ];
        }

        return $response;
    }

    /**
     * get the healt of wallet
     */
    public function health(bool $planned): float
    {
        $wallet = new Wallet(0);

        $accounts = Account::user()->get();
        foreach($accounts as $account) {
            $wallet->deposit($account->balance);
        }

        return $wallet->getBalance();
    }

}
