<?php
namespace backend\models;

use Yii;
use yii\base\Model;

class PaymentJson extends Payment
{

    public $firstName;
    public $lastName;
    public $salary;
    public $dateOfBirth;
    public $creditScore;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['firstName', "lastName", "salary", "dateOfBirth", "creditScore"], 'string'],
        ];
    }

    /**
     * Sending request
     *
     * @param $url
     * @return array
     */
    public function send($url)
    {
        try {
            $this->creditScore = Payment::$creditScoreMap[$this->creditScore];
            $data = self::prepareData($this->getAttributes());

            $headers = array(
                "Content-type: application/json",
                "Content-length: " . strlen($data),
                "Connection: close",
            );

            $response = Payment::sendCurl($data, $url, $headers);
            if ($response['success']) {
                $data = json_decode($response['data'], true);

                switch ($data['SubmitDataResult']) {
                    case self::RESULT_SOLD :
                        return [
                            'status' => self::$results[Payment::RESULT_SOLD],
                            'description' => ''
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
                            'description' => $data['SubmitDataErrorMessage'] ?? ''
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
     * Prepare data for sending
     *
     * @param $data
     * @return false|string
     */
    public static function prepareData($data)
    {
        if (isset($data['salary'])) {
            $data['Salary'] = $data['salary'];
            unset($data['salary']);
        }

        return json_encode([
            'userInfo' => $data
        ]);
    }

    /**
     * Server response
     *
     * @param $result
     * @return array
     */
    public function response($result)
    {
        $data = [];

        switch ($result) {
            case Payment::RESULT_SOLD:
                $data = [
                    'SubmitDataResult' => Payment::RESULT_SOLD,
                ];
                break;
            case Payment::RESULT_REJECT:
                $data = [
                    'SubmitDataResult' => Payment::RESULT_REJECT,
                ];
                break;
            case Payment::RESULT_ERROR:
                $data = [
                    'SubmitDataResult' => Payment::RESULT_ERROR,
                    'SubmitDataErrorMessage' => '',
                ];
                break;
        }

        return $data;
    }
}
