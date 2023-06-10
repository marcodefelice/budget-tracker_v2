<?php

namespace App\Stats\Services;

use App\BudgetTracker\Services\DebitService;
use App\BudgetTracker\Services\ExpensesService;
use App\BudgetTracker\Services\IncomingService;
use App\BudgetTracker\Services\TransferService;
use App\BudgetTracker\Models\Entry;
use App\BudgetTracker\DataObjects\Wallet;
use App\BudgetTracker\Models\Account;
use App\Http\Services\UserService;
use DateTime;

class StatsService
{

    private readonly string $startDate;
    private readonly string $endDate;
    private readonly string $startDatePassed;
    private readonly string $endDatePassed;

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

        $entry = new IncomingService();
        $entry->setDateStart($this->startDate)->setDateEnd($this->endDate);

        $entryOld = new IncomingService();
        $entryOld->setDateStart($this->startDatePassed)->setDateEnd($this->endDatePassed);

        if ($planning === true) {
            $entry->setPlanning($planning);
            $entryOld->setPlanning($planning);
        } else {
            $entry->setPlanning(false);
        }

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return Array
     */
    public function expenses(bool $planning): Array
    {
        $entry = new ExpensesService();
        $entry->setDateStart($this->startDate)->setDateEnd($this->endDate);

        $entryOld = new ExpensesService();
        $entryOld->setDateStart($this->startDatePassed)->setDateEnd($this->endDatePassed);

        if ($planning === true) {
            $entry->setPlanning($planning);
            $entryOld->setPlanning($planning);
        } else {
            $entry->setPlanning(false);
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
        $entry = new TransferService();
        $entry->setDateStart($this->startDate)->setDateEnd($this->endDate);

        $entryOld = new TransferService();
        $entryOld->setDateStart($this->startDatePassed)->setDateEnd($this->endDatePassed);

        if ($planning === true) {
            $entry->setPlanning($planning);
            $entryOld->setPlanning($planning);
        } else {
            $entry->setPlanning(false);
        }

        return ['total' => $entry->get()->toArray(),'total_passed' => $entryOld->get()->toArray()];

    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return Array
     */
    public function debit(bool $planning): array
    {
        $entry = new DebitService();
        $entry->setDateStart($this->startDate)->setDateEnd($this->endDate);

        $entryOld = new DebitService();
        $entryOld->setDateStart($this->startDatePassed)->setDateEnd($this->endDatePassed);

        if ($planning === true) {
            $entry->setPlanning($planning);
            $entryOld->setPlanning($planning);
        } else {
            $entry->setPlanning(false);
        }

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
            ->where('entries.confirmed', 1)
            ->where('entries.user_id', UserService::getCacheUserID())
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
