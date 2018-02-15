<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;


$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

$styles = <<< CSS
#check-code {
    background: rgba(0, 0, 0, 0.49);
}
#check-code .modal-dialog {
    margin: 30vh auto;
}

CSS;

$this->registerCss($styles);

/*$script = <<< JS
 jQuery('input[name="login-button"]');
        jQuery('#login-form').submit(function(e){
            e.preventDefault();
            var username = jQuery('#loginform-username').val();
            var pass = jQuery('#loginform-password').val();
            var data = 'username=' + username + '&pasword=' + pass;
            console.log(data);

            jQuery.ajax({
            url: '/newmodule/login/validate',
            type: 'POST',
            data: data,
            success: function(){

            },
            });
            alert('132');
        });

JS;*/
$script = <<< JS
$('form#login-form').on('submit', function(){

    var form = $(this);
    console.log(form);
    // return false if form still have some validation errors
    /*if (form.find('.has-error').length) {
        return false;
    }*/
    alert(form.attr('action'));

    // submit form
    $.ajax({
        url    : form.attr('action'),
        type   : 'post',
        data   : form.serialize(),
        success: function (response) {
            //console.log(response);
            var data = JSON.parse(response);
            if (data.success) {

            } else {
                var errorMsg = data.data['loginform-password'][0];
                jQuery('.field-loginform-password').addClass('has-error');
                jQuery('.field-loginform-password .help-block-error').html(errorMsg);
                console.log(data.data);
            }

            //console.log(data);
            if(data.modal){
                jQuery('#check-code').modal();
            }
            // do something with response
        },
        error  : function () {

        }
    });
    return false;
});

function checkCode() {
alert(132);
}

$('.check-google-code').on('click', function(e){
    e.preventDefault();
    var form = $('form#login-form');
    var code = $('#loginform-checkcode').val();
    var user = $('#loginform-username').val();
    $.ajax({
        url: 'login/check-code',
        type: 'post',
        //data: 'code=' + code + '&username' + user,
        data: form.serialize(),
        success: function(result){
            if(result == 'error') {
            jQuery('.field-loginform-checkcode').addClass('has-error');
            jQuery('.field-loginform-checkcode .help-block-error').html('Invalid code');
            }
            console.log(result);
        }
    });
});

JS;

$this->registerJs($script);
/*$hash = Yii::$app->getSecurity()->generatePasswordHash('codevery');
var_dump($hash);*/
?>
<div class="site-login container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>1 Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
        'enableAjaxValidation' => true,
        'action' => ['login/ajax-login'],
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'rememberMe')->checkbox([
        'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ]) ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button', 'value' => '1']) ?>
        </div>
    </div>

    <?php
    Modal::begin([
        'id' => 'check-code',
        'size' => 'modal-sm',
        'clientOptions' => [
            'backdrop' => false, 'keyboard' => true
        ]
    ]); ?>

    <?= $form->field($model, 'checkCode', ['template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>", 'labelOptions' => ['class' => 'col-lg-12'],])->textInput()->label('Google Authenticator code'); ?>

    <div>
        <!--<a href="#" class="btn btn-primary" onclick="checkCode();">Send</a>-->
        <a href="#" class="btn btn-primary check-google-code">Send</a>
    </div>

    <?php Modal::end(); ?>

    <?php ActiveForm::end(); ?>

    <!--<div class="col-lg-offset-1" style="color:#999;">
        You may login with <strong>admin/admin</strong> or <strong>demo/demo</strong>.<br>
        To modify the username/password, please check out the code <code>app\models\User::$users</code>.
    </div>-->
</div>
