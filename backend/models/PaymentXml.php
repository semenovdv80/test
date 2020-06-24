<?php
namespace backend\models;

use Yii;
use yii\base\Model;

class PaymentXml extends Payment
{
    public $firstName;
    public $lastName;
    public $salary;
    public $age = '';
    public $creditScore;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['firstName', "lastName", "salary", "creditScore"], 'string'],
            [['age'], 'safe'],
        ];
    }

    /**
     * Sending request
     * @param $url
     * @return array
     */
    public function send($url)
    {
        try {
            $this->creditScore = Payment::$creditScoreMap[$this->creditScore];
            $data = self::prepareData($this->getAttributes());

            $headers = array(
                "Content-type: text/xml",
                "Content-length: " . strlen($data),
                "Connection: close",
            );

            $response = Payment::sendCurl($data, $url, $headers);
            if ($response['success']) {
                $data = simplexml_load_string($response['data']);
                $json = json_encode($data);
                $data = json_decode($json, true);

                switch (strtolower($data['returnCodeDescription'])) {
                    case self::RESULT_SOLD :
                        return [
                            'status' => self::$results[Payment::RESULT_SOLD],
                            'description' => $data['transactionId'] ?? ''
                        ];
                        break;
                    case self::RESULT_REJECT :
                        return [
                            'status' => self::$results[Payment::RESULT_REJECT],
                            'description' => ''
                        ];
                        break;
                    case self::RESULT_ERROR :
                        return [
                            'status' => self::$results[Payment::RESULT_ERROR],
                            'description' => $data['returnError'] ?? ''
                        ];
                        break;
                }
            } else {
                return [
                    'status' => Payment::RESULT_ERROR,
                    'description' => $response['message']
                ];
            }
        } catch (\Exception $exception) {
            return [
                'status' => Payment::RESULT_ERROR,
                'description' => $exception->getMessage()
            ];
        }
    }

    /**
     * Prepare xml data
     * @param $data
     * @return mixed
     */
    public static function prepareData($data)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><userInfo/>');
        $xml->addAttribute('version', '1.6');
        foreach ($data as $key => $value) {
            $xml->addChild($key, $value);
        };

        return $xml->asXML();
    }

    /**
     * Response from server
     *
     * @param $result
     * @return mixed
     */
    public function response($result)
    {
        $data = [];

        switch ($result) {
            case Payment::RESULT_SOLD:
                $data = [
                    'returnCode' => 1,
                    'returnCodeDescription' => strtoupper(Payment::RESULT_SOLD),
                    'transactionId' => uniqid()
                ];
                break;
            case Payment::RESULT_REJECT:
                $data = [
                    'returnCode' => 0,
                    'returnCodeDescription' => strtoupper(Payment::RESULT_REJECT),
                ];
                break;
            case Payment::RESULT_ERROR:
                $data = [
                    'returnCode' => 0,
                    'returnCodeDescription' => strtoupper(Payment::RESULT_ERROR),
                    'returnError' => 'Lead not Found',
                ];
                break;
        }

        return self::prepareData($data);
    }
}
