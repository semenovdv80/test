<?php
namespace backend\controllers;

use backend\models\Payment;
use backend\models\PaymentJson;
use backend\models\PaymentXml;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'payment', 'response'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Payment request
     *
     * @param $type
     */
    public function actionPayment($type)
    {
        $data = [
            "firstName"   => "Vasya",
            "lastName"    => "Pupkin",
            "dateOfBirth" => "1984-07-31",
            "Salary"      => "1000",
            "creditScore" => "good"
        ];

        //Prepare data keys
        $data = array_combine(
            array_map('lcfirst', array_keys($data)),
            array_values($data)
        );

        $url = Url::to(['site/response', 'type' => $type], true);

        if ($type == Payment::PAYMENT_TYPE_XML) {
            $payment = new PaymentXml();
        } else {
            $payment = new PaymentJson();
        }

        $payment->load($data, '');

        $response = $payment->send($url);
        print_r($response);
    }

    /**
     * Response from server
     * @param $type
     * @param string $result
     * @return array|mixed
     */
    public function actionResponse($type, $result = Payment::RESULT_SOLD)
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        if ($type == Payment::PAYMENT_TYPE_XML) {
            $response->headers->set('Content-Type', 'text/xml');
            $payment = new PaymentXml();
        } else {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $payment = new PaymentJson();
        }

        return $payment->response($result);
    }
}
