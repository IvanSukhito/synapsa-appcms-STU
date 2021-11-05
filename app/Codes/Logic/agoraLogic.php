<?php

namespace App\Codes\Logic;

use App\Codes\Logic\agora\RtcTokenBuilder;

class agoraLogic
{
    protected $appId;
    protected $appCertificate;

    public function __construct()
    {
        $this->appId = env('AGORA_APP_ID');
        $this->appCertificate = env('AGORA_APP_CERT');
    }

    public function createRtcToken($channelName = 'demochannel', $uid = 12345678910, $expireTimeInSeconds = 3600)
    {
        $role = RtcTokenBuilder::RoleAttendee;
        $currentTimestamp = (new \DateTime("now", new \DateTimeZone('Asia/Jakarta')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        $token = RtcTokenBuilder::buildTokenWithUid($this->appId, $this->appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

        return $token;
    }

    public function getAppId()
    {
        return $this->appId;
    }

}
