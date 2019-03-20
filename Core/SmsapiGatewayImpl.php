<?php

namespace MauticPlugin\MauticSmsapiBundle\Core;

use Smsapi\Client\Feature\Sms\Bag\SendSmsBag;
use Smsapi\Client\Infrastructure\ResponseMapper\ApiErrorException;

class SmsapiGatewayImpl implements SmsapiGateway
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function isConnected(): bool
    {
        $service = $this->connection->smsapiClient();

        try {
            $service->profileFeature()->findProfile();
        } catch (ApiErrorException $apiErrorException) {
            return $apiErrorException->getCode() !== 401;
        }

        return true;
    }

    public function getSendernames(): array
    {
        $sendernames = $this->connection->smsapiClient()->smsFeature()->sendernameFeature()->findSendernames();

        $array = [];
        foreach ($sendernames as $sendername) {
            $array[$sendername->sender] = $sendername->sender;
        }

        return $array;
    }

    public function sendSms(string $phoneNumber, string $content, string $sendername)
    {
        $service = $this->connection->smsapiClient();
        $sms = new SendSmsBag();
        $sms->to = $phoneNumber;
        $sms->from = $sendername;
        $sms->encoding = 'utf-8';
        $sms->message = $content;

        $service->smsFeature()->sendSms($sms);
    }

    public function getProfile(): Profile
    {
        $service = $this->connection->smsapiClient();
        $profile = $service->profileFeature()->findProfile();

        return new Profile($profile->points);
    }
}
