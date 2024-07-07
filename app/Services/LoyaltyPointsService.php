<?php

namespace App\Services;

use App\Mail\LoyaltyPointsReceived;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class LoyaltyPointsService
{
    public function deposit(array $depositData): void
    {
        $accountType = $depositData['account_type'];
        $accountId = $depositData['account_id'];

        $account = $this->getAccount($accountType, $accountId);

        $transaction = LoyaltyPointsTransaction::performPaymentLoyaltyPoints(
            $account->id,
            $depositData['loyalty_points_rule'],
            $depositData['description'],
            $depositData['payment_id'],
            $depositData['payment_amount'],
            $depositData['payment_time']
        );

        Log::info($transaction);

        if (!empty($account->email) && $account->email_notification) {
            Mail::to($account)->send(
                new LoyaltyPointsReceived($transaction->points_amount, $account->getBalance())
            );
        }

        if (!empty($account->phone) && $account->phone_notification) {
            Log::info(
                'You received' . $transaction->points_amount . 'Your balance' . $account->getBalance()
            );
        }
    }

    public function cancel(array $cancelData): void
    {
        if ($transaction = LoyaltyPointsTransaction::where('id', '=', $cancelData['transaction_id'])->where(
            'canceled',
            '=',
            0
        )->first()) {
            $transaction->canceled = time();
            $transaction->cancellation_reason = $cancelData['cancellation_reason'];
            $transaction->save();
        } else {
            throw new RuntimeException('Transaction is not found');
        }
    }

    public function withdraw(array $withdrawData): void
    {
        $accountType = $withdrawData['account_type'];
        $accountId = $withdrawData['account_id'];

        $account = $this->getAccount($accountType, $accountId);

        if ($withdrawData['points_amount'] <= 0) {
            throw new RuntimeException('Wrong loyalty points amount: ' . $withdrawData['points_amount']);
        }

        if ($account->getBalance() < $withdrawData['points_amount']) {
            throw new RuntimeException('Insufficient funds: ' . $withdrawData['points_amount']);
        }

        $transaction = LoyaltyPointsTransaction::withdrawLoyaltyPoints(
            $account->id,
            $withdrawData['points_amount'],
            $withdrawData['description']
        );

        Log::info($transaction);
    }

    private function getAccount(string $accountType, $accountId)
    {
        $account = LoyaltyAccount::where($accountType, '=', $accountId)->first();

        if (empty($account)) {
            throw new RuntimeException('Account is not found');
        }

        if (!$account->active) {
            throw new RuntimeException('Account is not active');
        }

        return $account;
    }
}
