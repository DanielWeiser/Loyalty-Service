<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\LoyaltyPointsService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LoyaltyPointsController extends Controller
{
    public function __construct(private LoyaltyPointsService $loyaltyPointsService)
    {
    }

    public function deposit(DepositRequest $request): Response|Application|ResponseFactory
    {
        try {
            $depositArray = $request->validated();

            Log::info('Deposit transaction input: ' . print_r($depositArray, true));

            $this->loyaltyPointsService->deposit($depositArray);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response();
    }

    public function cancel(CancelRequest $request): Response|Application|ResponseFactory
    {
        try {
            $cancelArray = $request->validated();

            $this->loyaltyPointsService->cancel($cancelArray);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response();
    }

    public function withdraw(WithdrawRequest $request): Response|Application|ResponseFactory
    {
        try {
            $withdrawArray = $request->validated();

            Log::info('Withdraw loyalty points transaction input: ' . print_r($withdrawArray, true));

            $this->loyaltyPointsService->withdraw($withdrawArray);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return response($exception->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response();
    }
}
