<?php

namespace App\BudgetTracker\Services;

use App\BudgetTracker\Enums\EntryType;
use App\BudgetTracker\Enums\PlanningType;
use App\BudgetTracker\Models\PlannedEntries;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\Config\Exception\ValidationException;
use App\BudgetTracker\Models\Payee;
use App\Http\Services\UserService;
use App\BudgetTracker\Entity\Entries\PlannedEntry;
use DateTime;
use App\BudgetTracker\Models\SubCategory;
use App\BudgetTracker\Models\Account;
use App\BudgetTracker\Models\Currency;
use App\BudgetTracker\Models\PaymentsTypes;
use Exception;

/**
 * Summary of SaveEntryService
 */
class PlanningRecursivelyService extends EntryService
{

  /**
   * save a resource
   * @param array $data
   * @param EntryType|null $type
   * @param Payee|null $payee
   * 
   * @return void
   */
  public function save(array $data, EntryType|null $type = null, Payee|null $payee = null): void
    {
        try {

            Log::debug("save planning recursively -- " . json_encode($data));

            self::validate($data);

            if ($data['amount'] < 0) {
                $type = EntryType::Expenses->value;
            } else {
                $type = EntryType::Incoming->value;
            }

            $entry = new PlannedEntries(['type' => $type, 'planning' => PlanningType::from($data['planning'])]);
            if (!empty($data['id'])) {
                $entry = PlannedEntries::find($data['id']);
            }

            $entryData = $this->makeObj($data);
            $entryData = $entryData->toArray();

            $entry->account_id = $entryData['account_id'];
            $entry->amount = $entryData['amount'];
            $entry->category_id = $entryData['category_id'];
            $entry->currency_id = $entryData['currency_id'];
            $entry->date_time = $entryData['date_time'];
            $entry->note = $entryData['note'];
            $entry->payment_type = $entryData['payment_type'];
            $entry->end_date_time = $entryData['end_date_time'];
            $entry->planning = $entryData['planning'];
            $entry->user_id = empty($entryData['user_id']) ? UserService::getCacheUserID() : $entryData['user_id'];

            $entry->save();

            $this->attachLabels($data['label'], $entry);
            
        } catch (\Exception $e) {
            $error = uniqid();
            Log::error("$error " . $e->getMessage());
            throw new \Exception("Ops an errro occurred ".$error);
        }
    }

    /**
     * read a resource
     * @param int $id of resource
     * 
     * @return object with a resource
     * @throws \Exception
     */
    public function read(int $id = null): object
    {
        Log::debug("read planning recursively  -- $id");

        $entries = PlannedEntries::user()->get();
        $resourses = [];
        foreach($entries as $entry) {
            $plannedEntry = $this->makeObj($entry->toArray());
            $resourses[] = $plannedEntry->get();
        }

        $result = new \stdClass();
        $result->data = $resourses;

        return $result;
    }

    /**
     * read a resource
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    public static function validate(array $data): void
    {
        $rules = [
            'id' => ['integer'],
            'date_time' => ['date', 'date_format:Y-m-d H:i:s'],
            'account_id' => 'required|boolean',
            'name' => 'string'
        ];

        Validator::validate($data, $rules);
    }

    protected function makeObj(array $data): PlannedEntry
    {
        $endDateTime = empty($data['end_date_time']) ? null : new DateTime($data['end_date_time']);
        $planning = PlanningType::from($data['planning']);
        $label = empty($data['label']) ? [] : $data['label'];

        $plannedEntry = new PlannedEntry(
            $data['amount'],
            Currency::findOrFail($data['currency_id']),
            $data['note'],
            SubCategory::with('category')->findOrFail($data['category_id']),
            Account::findOrFail($data['account_id']),
            PaymentsTypes::findOrFail($data['payment_type']),
            new DateTime($data['date_time']),
            $label,
            $data['confirmed'],
            $data['waranty'],
            0,
            new \stdClass(),
          );

          $plannedEntry->setPlanning($planning)->setEndDateTime($endDateTime);
          return $plannedEntry;
    }

}