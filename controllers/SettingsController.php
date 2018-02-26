<?php

namespace devmary\auth\controllers;

use Yii;
use yii\web\Controller;
use devmary\auth\models\SettingsForm;
use devmary\auth\GoogleAuthenticator;
use devmary\auth\models\GoogleAuth;
use app\models\User;


/**
 * Default controller for the `auth` module
 */
class SettingsController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $settings = new SettingsForm();
        $user = User::findByUsername($settings->username);
        $gauth = new GoogleAuth();
        $guser = $gauth->findOne(['user_id'=>$user->id]);

        if($guser) {
            $settings->active = $guser->status;
            $settings->secretCode = $guser->secret_code;
        }
        if ($settings->load(Yii::$app->request->post())) {
            $SettingsForm = Yii::$app->request->post('SettingsForm');
            $msg = 'Settings saved successfully';

            $settings->active = $SettingsForm['active'];
            $settings->secretCode = $SettingsForm['secretCode'];

            $settings->qrUrl = $SettingsForm['qrUrl'];
            return $this->render('settings', [
                'settings' => $settings,
                'msg' => $msg
            ]);
        }
        $settings['qrUrl'] = $settings->getQR();
        $settings['active'] = $settings->getStatus2fa();

        return $this->render('settings', [
            'settings' => $settings,
        ]);
    }

    public function actionSecret(){
        $this->layout = false;
        $googleAuth = new GoogleAuthenticator;
        $secret = $googleAuth->createSecret();
        $qrUrl = $googleAuth->getQRCodeGoogleUrl(Yii::$app->name, $secret);
        $result = array(
            'secret' => $secret,
            'qrUrl' => $qrUrl
        );
        $result = json_encode($result);

        return $result;
    }

    public function actionBackupCode(){
        $six_digit_random_number = mt_rand(10000000, 99999999);
        return $six_digit_random_number;
    }

    public function actionSubmit() {
        $error = false;//TODO переменная всегда false?
        $result = array();
        if( Yii::$app->request->isAjax ) {
            $model = new SettingsForm();
            if ($model->load(Yii::$app->request->post())) {
                $settings = Yii::$app->request->post('SettingsForm');
                $status = $settings['active'];
                $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
                $secret = $settings['secretCode'];
                $backup_code = $settings['backupCode'];
                $password = $settings['password'];
                if($model->validate()){
                    if($status) {//TODO я правильно понял что если прийдеи active true - вообще ничего не произойдет?
                        $result = array(
                            'error' => $error,
                            'modal' => true
                        );
                    } else {
                        $user_id = Yii::$app->getUser()->identity->getId();
                        $gauth = new GoogleAuth();
                        $guser = GoogleAuth::findOne(['user_id'=>$user_id]);
                        if($guser) {//TODO зачем дублировать присваение? почему просто не переопределить 
                            $guser->user_id = $user_id;
                            $guser->secret_code = $secret;
                            $guser->status = (int)$status;
                            $guser->backup_code = (int)$backup_code;
                            $guser->save();

                        } else {
                            $gauth->user_id = $user_id;
                            $gauth->secret_code = $secret;
                            $gauth->status = (int)$status;
                            $gauth->backup_code = (int)$backup_code;
                            $gauth->save();

                        }
                        $result = array(
                            'error' => $error,
                            'modal' => false
                        );
                    }
                } else {
                    $result = array(
                        'error' => $model->errors,
                        'modal' => false
                    );
                }
            }
        }

        return json_encode($result);
    }

    public function actionCheck() {
        $result = array();
        if( Yii::$app->request->isAjax ) {
            $model = new SettingsForm();
            if ($model->load(Yii::$app->request->post())) {

                $settings = Yii::$app->request->post('SettingsForm');
                $secret = $settings['secretCode'];
                $status = Yii::$app->request->post('status');
                $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
                $backup_code = Yii::$app->request->post('backup_code');
                $verify = $model->verifyCode($settings['checkCode'], $secret);
                if( $verify ) {
                    $user_id = Yii::$app->getUser()->identity->getId();
                    $gauth = new GoogleAuth();
                    $guser = GoogleAuth::findOne(['user_id'=>$user_id]);

                    if($guser) {//TODO зачем дублировать присваение? почему просто не переопределить 
                        $guser->user_id = $user_id;
                        $guser->secret_code = $secret;
                        $guser->status = (int)$status;
                        $guser->backup_code = (int)$backup_code;
                        $guser->active = true;
                        $guser->save();

                    } else {
                        $gauth->user_id = $user_id;
                        $gauth->secret_code = $secret;
                        $gauth->status = (int)$status;
                        $gauth->backup_code = (int)$backup_code;
                        $gauth->active = true;
                        $gauth->save();

                    }
                    $result = array(
                        'error' => false
                    );
                } else {
                    $result = array(
                        'error' => $model->errors
                    );
                }
            }
        }

        return json_encode($result);
    }



    /**
     * Settings page
     */
    public function actionSettings(){

        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $settings = new SettingsForm();
        if ($settings->load(Yii::$app->request->post()) ) {
            return $this->goBack();
        }
        $settings['qrUrl'] = $settings->getQR();

        return $this->render('settings', [
            'settings' => $settings,
        ]);
    }

    public function actionActivate() {

        if( Yii::$app->request->isAjax ) {
            if (!Yii::$app->user->isGuest) {
                $user_id = Yii::$app->getUser()->identity->getId();
                $guser = GoogleAuth::findOne(['user_id' => $user_id]);
                if ($guser) {
                    if ($guser->active) {
                        $guser->status = true;
                        $guser->save();
                        return true;//TODO А если save не прошел?
                    }
                }
            }
        }

        return false;
    }
}
