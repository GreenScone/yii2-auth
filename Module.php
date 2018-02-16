<?php

namespace devmary\auth;

/**
 * auth module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'devmary\auth\controllers';

    /**
     * @inheritdoc
     */
//    public $defaultRoute = 'devmary\auth\controllers\SettingsController';
    public $defaultRoute = 'settings';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
