<?php

namespace App\BudgetTracker\Http\Controllers;

use App\BudgetTracker\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BudgetTracker\Interfaces\ControllerResourcesInterface;
use App\BudgetTracker\Models\Entry;
use App\BudgetTracker\Models\Incoming;
use App\BudgetTracker\Services\AccountsService;
use App\BudgetTracker\Services\IncomingService;
use League\Config\Exception\ValidationException;
use App\BudgetTracker\Services\ResponseService;

class IncomingController extends Controller implements ControllerResourcesInterface
{
	//
	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Exception
	 */
	public function index(): \Illuminate\Http\JsonResponse
	{
		$service = new IncomingService();
		$incoming = $service->read();
		return response()->json(new ResponseService($incoming));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request): \Illuminate\Http\Response
	{
		try {
			$service = new IncomingService();
			$service->save($request->toArray());
			return response('All data stored');
		} catch (\Exception $e) {
			return response($e->getMessage(), 500);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show(int $id): \Illuminate\Http\JsonResponse
	{
		$service = new IncomingService();
		$incoming = $service->read($id);
		return response()->json(new ResponseService($incoming));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(int $id): \Illuminate\Http\Response
	{
		$entry = Entry::findOrFail($id);
		try {
			Incoming::destroy($id);
			AccountsService::updateBalance($entry->amount * -1,$entry->account_id);
			return response("Resource is deleted");
		} catch (\Exception $e) {
			return response($e->getMessage());
		}
	}
}
