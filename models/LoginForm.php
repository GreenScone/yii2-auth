<?php

namespace app\modules\auth\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
//use yii\web\User;
use app\modules\auth\GoogleAuthenticator;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $checkCode;
    public $google_auth_2fa = false;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /*$user = new User;
        $user->on(User::EVENT_BEFORE_LOGIN, function($event){
            var_dump($event);
        });*/
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            if($this->rememberMe){
                $u = $this->getUser();
                $u->generateAuthKey();
                $u->save();
            }
//            var_dump($this->rememberMe);die;
            //var_dump($this->getUser());
//            if ($this->_user['attributes']['active_2fa']) {
//                return true;
//                /*return $this->render('index', [
//                    'modal' => true,
//                ]);*/
//                //var_dump(1);
//            } else {
//                return false;
//                //return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
//            }
            //var_dump($this->_user['attributes']['id']);
            //var_dump($this->_user['attributes']['active_2fa']);
            //var_dump($this->_user['attributes']['secret_2fa']);
//            var_dump($this->getUser());die;
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
//            return true;
        }
        return false;
    }

    public function checkCode($secret, $code) {
        $googleauth = new GoogleAuthenticator;
        $verify = $googleauth->verifyCode($secret, $code, 0);
        return $verify;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserIdentity::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * Finds user by [[id]]
     *
     * @return User|null
     */
    public function getUserById($id){
        $user = UserIdentity::findIdentity($id);
        return $user;
    }
}
