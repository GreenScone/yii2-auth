<?php

namespace app\modules\auth;

/**
 * auth module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\auth\controllers';

    /**
     * @inheritdoc
     */
//    public $defaultRoute = 'app\modules\auth\controllers\SettingsController';
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
