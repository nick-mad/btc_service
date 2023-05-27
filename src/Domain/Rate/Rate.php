<?php

namespace App\Domain\Rate;

class Rate
{
    /**
     * @throws InvalidStatusException
     */
    public static function get(): string
    {
        $rate = self::getRateFomCoingecko();
        if ($rate) {
            return $rate;
        }

        $rate = self::getRateFomCoincap();
        if ($rate) {
            return $rate;
        }

        throw new InvalidStatusException();
    }

    private static function getRateFomCoingecko(): string
    {
        $response = self::request('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=uah');

        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        }

        if (!empty($data['bitcoin']['uah'])) {
            return (string)$data['bitcoin']['uah'];
        }

        return '';
    }

    private static function getRateFomCoincap()
    {
        $response = self::request('https://api.coincap.io/v2/rates/bitcoin');

        try {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        }
        $btc = $response['data']['rateUsd'] ?? 0;
        $response = self::request('https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5');
        try {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
        }
        $uah = $response[1]['buy'] ?? 0;
        $btc_uah = ceil((int)$btc * $uah);
        if ($btc_uah > 0) {
            return (string)$btc_uah;
        }

        return '';
    }

    private static function request($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
