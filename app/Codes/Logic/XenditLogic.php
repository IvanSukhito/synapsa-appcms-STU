<?php

namespace App\Codes\Logic;

use Xendit\EWallets;
use Xendit\QRCode;
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

    public function createVA($params)
    {
        try {
            Xendit::setApiKey($this->XENDIT_SECRET_KEY);
            $result = VirtualAccounts::create($params);
        }
        catch (\Exception $e) {
            $result = (object)[
                'status' => "GAGAL",
                'message' => $e->getMessage()
            ];
        }

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

    public function createEWalletOVO($params)
    {
//        $ch = curl_init();
//        curl_setopt( $ch,CURLOPT_URL, $this->XENDIT_URL.'/ewallets/charges' );
//        curl_setopt( $ch,CURLOPT_POST, true );
//        curl_setopt($ch, CURLOPT_USERPWD, $this->XENDIT_SECRET_KEY . ":");
//        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
//        curl_setopt( $ch,CURLOPT_POSTFIELDS, http_build_query($params));
//        $result = curl_exec($ch );
//        curl_close( $ch );
//
//        if (is_string($result)) {
//            $result = json_decode($result);
//        }

        return $this->createEWallet($params);
    }

    public function createEWalletDANA($params)
    {
        return $this->createEWallet($params);
    }

    public function createEWalletLINKAJA($params)
    {
        return $this->createEWallet($params);
    }

    public function createQrQris($params)
    {
        Xendit::setApiKey($this->XENDIT_SECRET_KEY);
        return QRCode::create($params);
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
