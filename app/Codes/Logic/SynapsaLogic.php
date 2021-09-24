<?php

namespace App\Codes\Logic;

use App\Codes\Models\V1\LogServiceTransaction;

class SynapsaLogic
{
    public function __construct()
    {
    }

    public function createPayment($payment, $transaction, $additional)
    {
        if ($payment->service == 'xendit') {
            $xendit = new XenditLogic();
            $result = false;
            switch ($payment->type_payment) {
                case 'va_bca':
                case 'va_bri':
                case 'va_bni':
                case 'va_bjb':
                case 'va_cimb':
                case 'va_mandiri':
                case 'va_permata':
                    $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                    $result = $xendit->createVA($transaction->code, $getTypePayment, $transaction->total, $additional['name'] ?? '');
                    break;
                case 'ew_ovo': $result = $xendit->createEWalletOVO($transaction->code, $transaction->total, $transaction->receiver_phone);
                    break;
                case 'ew_dana': $result = $xendit->createEWalletDANA($transaction->code, $transaction->total);
                    break;
                case 'ew_linkaja': $result = $xendit->createEWalletLINKAJA($transaction->code, $transaction->total, $transaction->receiver_phone);
                    break;
            }

            if ($result) {
                $getTypePayment = strtoupper(substr($payment->type_payment, 3));
                LogServiceTransaction::create([
                    'transaction_id' => $transaction->id,
                    'transaction_refer_id' => $transaction->id,
                    'service' => 'xendit',
                    'type_payment' => $getTypePayment,
                    'type_transaction' => 'create',
                    'results' => $result
                ]);
            }

        }
    }
}
