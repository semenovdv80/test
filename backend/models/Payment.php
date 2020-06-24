<?php
namespace backend\models;

use Yii;
use yii\base\Model;

class Payment extends Model
{
    //Payment types
    const PAYMENT_TYPE_XML  = 'xml';
    const PAYMENT_TYPE_JSON = 'json';

    //Credit score
    const CREDIT_SCORE_GOOD = 'good';
    const CREDIT_SCORE_BAD  = 'bad';

    /** @var array Credit Scope Map  */
    public static $creditScoreMap = [
        self::CREDIT_SCORE_GOOD => 700,
        self::CREDIT_SCORE_BAD  => 300,
    ];

    //Results
    const RESULT_SOLD   = 'success';
    const RESULT_REJECT = 'reject';
    const RESULT_ERROR  = 'error';

    /** @var array List of available results */
    public static $results = [
        self::RESULT_SOLD   => 'sold',
        self::RESULT_REJECT => 'reject',
        self::RESULT_ERROR  => 'error',
    ];

    /**
     * Send curl request
     *
     * @param $data
     * @param $url
     * @param $headers
     * @return array
     */
    public static function sendCurl($data, $url, $headers)
    {
        $response = [];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response['data'] = curl_exec($ch);
        if (curl_errno($ch)) {
            $response['success'] = false;
            $response['message'] = curl_error($ch);
        } else {
            $response['success'] = true;
            curl_close($ch);
        }

        return $response;
    }
}
