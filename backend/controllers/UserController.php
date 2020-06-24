<?php
namespace backend\controllers;

use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class UserController extends Controller
{
    public $layout=false;
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
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
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
     * Displays users list.
     *
     * @return string
     */
    public function actionList()
    {
        $users = User::getList();
        return $this->render('/admin/user/list.twig', ['users' => $users]);
    }

}
