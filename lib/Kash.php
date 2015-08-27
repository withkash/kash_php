<?php

namespace Kash;

class Kash
{
    private static $apiKey;
    private static $apiEndpoint;

    const TEST_API_ENDPOINT = 'https://api-test.withkash.com/v1';
    const PROD_API_ENDPOINT = 'https://api.withkash.com/v1';

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function getApiEndpoint()
    {
        if (self::$apiEndpoint)
        {
            return self::$apiEndpoint;
        }
        else if (substr(self::$apiKey, 0, 7) === "sk_test")
        {
            return self::TEST_API_ENDPOINT;
        }
        else
        {
            return self::PROD_API_ENDPOINT;
        }
    }

    public static function setApiEndpoint($apiEndpoint)
    {
        self::$apiEndpoint = $apiEndpoint;
    }

    public static function authorizeAmount($customerId, $amount)
    {
        $result = self::post("/authorizations", array(
            "customer_id" => $customerId,
            "amount" => $amount
        ));

        if ($result->statusCode !== 200)
        {
            if ($result->statusCode === 402)
            {
                throw new Error\NotSufficientFunds($result->result->error, $result->statusCode);
            }
            else if ($result->statusCode === 410)
            {
                throw new Error\RelinkRequired($result->result->error, $result->statusCode);
            }
            else
            {
                throw new Error\Unexpected($result->result->error, $result->statusCode);
            }
        }
        else
        {
            return $result->result;
        }
    }

    public static function cancelAuthorization($authorizationId)
    {
        $result = self::del("/authorization/" . $authorizationId);

        if ($result->statusCode !== 200)
        {
            throw new Error\Unexpected($result->result->error, $result->statusCode);
        }
        else
        {
            return $result->result;
        }
    }

    public static function createTransaction($authorizationId, $amount)
    {
        $result = self::post("/transactions", array(
            "authorization_id" => $authorizationId,
            "amount" => $amount
        ));

        if ($result->statusCode !== 200)
        {
            throw new Error\Unexpected($result->result->error, $result->statusCode);
        }
        else
        {
            return $result->result;
        }
    }

    public static function refundTransaction($transactionId, $amount)
    {
        $result = self::post("/refunds", array(
            "transaction_id" => $transactionId,
            "amount" => $amount
        ));

        if ($result->statusCode !== 200)
        {
            throw new Error\Unexpected($result->result->error, $result->statusCode);
        }
        else
        {
            return $result->result;
        }
    }

    private static function post($path, $payload)
    {
        $url = self::getApiEndpoint() . $path;

        $ch = curl_init();

        $json = json_encode($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::getApiKey() . ":");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($json)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $result = json_decode($result);

        curl_close($ch);

        return (object)array("result" => $result, "statusCode" => $statusCode);
    }

    private static function del($path)
    {
        $url = self::getApiEndpoint() . $path;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, self::getApiKey() . ":");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $result = json_decode($result);

        curl_close($ch);

        return (object)array("result" => $result, "statusCode" => $statusCode);
    }
}
