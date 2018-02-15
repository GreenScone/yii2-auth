<?php

namespace app\modules\auth\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
//use yii\web\User;
use app\modules\auth\GoogleAuthenticator;
//use app\modules\auth\models\UserAuth;
use app\modules\auth\models\GoogleAuth;
use yii\data\ActiveDataProvider;
use app\models\UserIdentity;

class SettingsForm extends Model
{
    public $username;
    public $active;
    public $secretCode;
    public $checkCode;
    public $qrUrl;
    public $password;
    public $backupCode;

    private $_user;

    public function init()
    {
        $this->username = Yii::$app->user->identity->username;
        $user = $this->getUser();
        //var_dump($user);
        $this->_user = UserIdentity::findByUsername($this->username);
        //$this->password = Yii::$app->user->identity->password;
        //var_dump($this->_user);
        //var_dump(UserIdentity::findByUsername($this->username));
        $this->backupCode = $this->getBackupCode();
    }

    public function rules()
    {
        return [
            [['password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            //['checkcode', 'verifyCode'],
            ['backupCode', 'number'],
        ];
    }



    public function getSecretCode(){

        $user_id = Yii::$app->user->identity->getId();
        $googleAuth = new GoogleAuthenticator;
        $gauth = new GoogleAuth();
        //var_dump($user_id);
        $user = $gauth->findOne(['user_id'=>$user_id]);
        if($user) {
            $this->secretCode = $user->secret_code;
           // var_dump('$user');
        } else {
            $this->secretCode = $googleAuth->createSecret();
            //var_dump('not found');
        }

        //var_dump($user);

        //var_dump(Yii::$app->user->identity->secret_2fa);
        //var_dump(Yii::$app->user->getIdentity());


        /*if(Yii::$app->user->identity->secret_2fa) {
            $this->secretCode = Yii::$app->user->identity->secret_2fa;
        } else {
            $this->secretCode = $googleAuth->createSecret();
        }*/

        return $this->secretCode;
    }

    public function getQR(){
        $secret = $this->getSecretCode();
        $googleAuth = new GoogleAuthenticator;
        $this->qrUrl = $googleAuth->getQRCodeGoogleUrl(Yii::$app->name, $secret);
        return $this->qrUrl;
    }

    public function getStatus2fa(){
        $gauth = new GoogleAuth();
        $user = $gauth->findOne(['user_id' => $this->_user->id]);
        if($user) {
            $this->active = $user->status;
        }

        return $this->active;
    }


   /* public function saveUserField(){
        $user = $this->getUser();
        var_dump($user);
    }*/

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserIdentity::findByUsername($this->username);
        }

        return $this->_user;
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->_user;

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect password.');
            }
        }
    }
    /*public function validatePassword($user_pass, $password)
    {
       return \Yii::$app->security->validatePassword($user_pass, $password);
    }*/

    public function verifyCode($code, $secret) {
        if (!$this->hasErrors()) {
            $googleAuth = new GoogleAuthenticator;
            $checkResult = $googleAuth->verifyCode($secret, $code, 0);
            if(!$checkResult) {
                $this->addError('checkcode', 'Incorrect code.');
                return false;
            } else {
                return true;
            }
        }
    }

    public function getBackupCode() {
        $user_id = Yii::$app->user->identity->getId();
        $gauth = new GoogleAuth();
        $user = $gauth->findOne(['user_id'=>$user_id]);
        if($user) {
            $this->backupCode = $user->backup_code;
        } else {
            $this->backupCode = mt_rand(10000000, 99999999);
        }

        return $this->backupCode;
    }


}