<?php

namespace App\Stats\Controllers;

use App\BudgetTracker\DataObjects\Wallet;
use App\BudgetTracker\Http\Controllers\Controller;
use App\BudgetTracker\Models\Account;
use App\BudgetTracker\Services\ResponseService;
use App\Helpers\EntriesMath;
use App\Helpers\MathHelper;
use \Illuminate\Http\JsonResponse;
use App\Stats\Services\StatsService;
use DateTime;

class StatsController extends Controller
{
    private readonly string $startDate;
    private readonly string $endDate;


    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return JsonResponse
     */
    public function incoming(bool $planning): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);
        $result = $service->incoming($planning);

        return response()->json(
            new ResponseService(
                $this->buildResponse($result['total'], $result['total_passed'])
            )
        );
    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return JsonResponse
     */
    public function expenses(bool $planning): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);
        $result = $service->expenses($planning);

        return response()->json(
            new ResponseService(
               $this->buildResponse($result['total'], $result['total_passed'])
            )
        );
    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return JsonResponse
     */
    public function transfer(bool $planning): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);
        $result =$service->transfer($planning);

        return response()->json(
            new ResponseService(
               $this->buildResponse($result['total'], $result['total_passed'])
            )
        );
    }

    /**
     * retrive data
     * @param bool $planning
     * 
     * @return JsonResponse
     */
    public function debit(bool $planning): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);
        $result =$service->debit($planning);

        return response()->json(
            new ResponseService(
               $this->buildResponse($result['total'], $result['total_passed'])
            )
        );
    }

    /**
     * retrive total wallet sum
     */
    public function total(bool $planning): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);

        return response()->json(
            new ResponseService(
                [
                    'total' => $service->total($planning),
                ]
            )
        );
    }

    /** 
     * retrive all accounts
     */
    public function wallets(): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);

        return response()->json(new ResponseService(
            $service->wallets(Account::user()->get())
        ));
    }

    /**
     * build stats standard response
     * @param array $data
     * @param array $dataOld
     * 
     * @return array
     */
    private function buildResponse(array $data, array $dataOld)
    {
        $wallet = new Wallet();
        $wallet->sum($data);

        $walletPassed = new Wallet();
        $walletPassed->sum($dataOld);

        $firstValue = $wallet->getBalance();
        $secondValue = $walletPassed->getBalance();

        return [
            'total' => $firstValue,
            'total_passed' => $secondValue,
            'percentage' => MathHelper::percentage($firstValue, $secondValue)
        ];
    }

    /**
     * get the healt of wallet
     */
    public function health(bool $planned): JsonResponse
    {
        $service = new StatsService($this->startDate, $this->endDate);

        return response()->json(
            new ResponseService(
                [
                    'total' => $service->health($planned),
                ]
            )
        );
    }
}
