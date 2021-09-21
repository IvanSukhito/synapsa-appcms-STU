<?php

namespace App\Codes\Logic;

use Xendit\EWallets;
use Xendit\VirtualAccounts;
use Xendit\Xendit;

class XenditLogic
{

    private $XENDIT_URL;
    private $XENDIT_SECRET_KEY;
    private $XENDIT_PUBLIC_KEY;
    private $XENDIT_CALLBACK_KEY;

    public function __construct()
    {
        $this->XENDIT_URL = 'https://api.xendit.co';
        $this->XENDIT_SECRET_KEY = env('XENDIT_SECRET_KEY');
        $this->XENDIT_PUBLIC_KEY = env('XENDIT_PUBLIC_KEY');
        $this->XENDIT_CALLBACK_KEY = env('XENDIT_CALLBACK_KEY');
    }

    public function getPaymentChannel()
    {
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/payment_channels' );
        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch );
        curl_close( $ch );

        if (is_string($result)) {
            $result = json_decode($result);
        }

        return $result;

    }

    public function createVA($transactionId, $codeBank, $amount, $name)
    {
        $params = [
            'external_id' => "va-".$transactionId,
            'bank_code' => $codeBank,
            'name' => $name,
            'expected_amount' => $amount
        ];

        Xendit::setApiKey($this->XENDIT_SECRET_KEY);
        $result = VirtualAccounts::create($params);

        return $result;
    }

    public function getInfoVa($id)
    {
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/callback_virtual_accounts/'.$id );
        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch );
        curl_close( $ch );

        if (is_string($result)) {
            $result = json_decode($result);
        }

        return $result;
    }

    public function simulatePaymentVA($externalId, $amount)
    {
        $params = [
            'amount' => $amount
        ];

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/callback_virtual_accounts/external_id='.$externalId.'/simulate_payment' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($ch );
        curl_close( $ch );

        if (is_string($result)) {
            $result = json_decode($result);
        }

        return $result;
    }

    public function createEWalletOVO($transactionId, $codeChannel, $amount, $phone)
    {
        $params = [
            'external_id' => 'ew-'.$transactionId,
            'currency' => 'IDR',
            'amount' => $amount,
            'phone' => $phone,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => $codeChannel,
            'ewallet_type' => $codeChannel,
            'channel_properties' => [
                'success_redirect_url' => route('api.redirectApps'),
            ],
            'metadata' => [
                'branch_code' => 'tree_branch'
            ]
        ];

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/ewallets/charges' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($ch );
        curl_close( $ch );

        if (is_string($result)) {
            $result = json_decode($result);
        }
        dd($result);

        return $this->createEWallet($params);
    }

    public function createEWalletDANA($transactionId, $codeChannel, $amount)
    {
        $params = [
            'external_id' => 'ew-'.$transactionId,
            'currency' => 'IDR',
            'amount' => $amount,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => $codeChannel,
            'ewallet_type' => $codeChannel,
            'callback_url' => route('api.redirectApps'),
            'redirect_url' => route('api.redirectApps'),
            'channel_properties' => [
                'success_redirect_url' => route('api.redirectApps'),
            ],
            'metadata' => [
                'branch_code' => 'tree_branch'
            ]
        ];

        $this->createEWallet($params);
    }

    public function createEWalletLINKAJA($transactionId, $codeChannel, $amount, $phone, $items)
    {
        $params = [
            'external_id' => 'ew-'.$transactionId,
            'currency' => 'IDR',
            'amount' => $amount,
            'phone' => $phone,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => $codeChannel,
            'ewallet_type' => $codeChannel,
            'items' => [
                [
                    'name' => 'Item 1',
                    'quantity' => 1,
                    'price' => $amount
                ]
            ],
            'callback_url' => route('api.redirectApps'),
            'redirect_url' => route('api.redirectApps'),
            'channel_properties' => [
                'success_redirect_url' => route('api.redirectApps'),
            ],
            'metadata' => [
                'branch_code' => 'tree_branch'
            ]
        ];

        $this->createEWallet($params);
    }

    public function createEWallet($params)
    {
        Xendit::setApiKey($this->XENDIT_SECRET_KEY);
        $result = EWallets::create($params);

        return $result;
    }

    public function infoEWallet($id)
    {
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/ewallets/charges/'.$id );
        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch );
        curl_close( $ch );

        if (is_string($result)) {
            $result = json_decode($result);
        }

        return $result;
    }

    public function createQRis()
    {

    }

    public function infoQRis($id)
    {

    }

    public function createCC()
    {

    }

    public function infoCC($id)
    {

    }

    public function createRetailOutlet()
    {

    }

    public function infoRetailOutlet($id)
    {

    }

}