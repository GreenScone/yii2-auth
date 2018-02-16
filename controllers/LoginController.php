<?php

namespace devmary\auth\controllers;

use Yii;
use app\models\LoginForm;
use yii\web\Response;
use devmary\auth\models\GoogleAuth;
use devmary\auth\GoogleAuthenticator;

class LoginController extends \yii\web\Controller
{
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionAjaxLogin(){

        if( Yii::$app->request->isAjax ){
            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    $user = $model->getUser();
                    $guser = GoogleAuth::findOne(['user_id'=>$user->id]);
                    if($guser && $guser->status) {
                        $response = [
                            'modal' => true,
                            'success' => true
                        ];
                        return json_encode($response);
                    } else {
                        $model->login();
                        return $this->goBack();
                    }

                } else {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    $result = array(
                        'data' => \yii\widgets\ActiveForm::validate($model),
                        'success' => false
                    );
                    return json_encode($result);
                }
            }
        }
        else {
            throw new \yii\web\HttpException(404 ,'Page not found');
        }
    }

    public function actionCheckCode() {
        $result = '';

        if (Yii::$app->request->isAjax) {
            $model = new LoginForm();

            if ($model->load(Yii::$app->request->post())) {
                $user = $model->getUser();

                $guser = GoogleAuth::findOne(['user_id' => $user->id]);
                $code =Yii::$app->request->post('code');

                $googleAuth = new GoogleAuthenticator;
                $checkResult = $googleAuth->verifyCode($guser->secret_code, $code, 0);
                if( ! $checkResult ){
                    if($code == $guser->backup_code) {
                        $checkResult = true;
                        $guser->backup_code = mt_rand(10000000, 99999999);
                        $guser->save();
                    }
                }

                if($checkResult) {
                    $result = 'success';
                    $model->login();
                    return $this->goBack();
                } else {
                    $result = 'error';
                }
            }
        }

        return $result;
    }

}
