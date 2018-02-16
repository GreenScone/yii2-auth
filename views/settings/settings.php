<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $settings devmary\auth\models\SettingsForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;

$this->title = 'Settings';
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

$script = <<< JS

jQuery('#check-google-code').submit(function(e){
    e.preventDefault();
    var form = $(this);
    var backup_code = $('#settingsform-backupcode').val();
    var status = $('#settingsform-active').is(':checked');
    $.ajax({
        url: '/auth/settings/check',
        type: 'POST',
        data: 'status=' + status + '&backup_code='+ backup_code + '&' + form.serialize(),
        beforeSend: function(){
            jQuery('.field-settingsform-checkcode').removeClass('has-error');
            jQuery('.field-settingsform-checkcode .help-block-error').html('')
        },
        success: function(result) {
            var data = JSON.parse(result);
            if(data.error) {
                jQuery('.field-settingsform-checkcode').addClass('has-error');
                jQuery('.field-settingsform-checkcode .help-block-error').html(data.error.checkcode[0]);
            } else {
                jQuery('#auth-2fa').submit();
            }
        }
    });
});

JS;

$this->registerJs($script);
?>
<script>
    function createNewSecret(){
        $.ajax({
            url: '/auth/settings/secret',
            type: 'POST',
            beforeSend: function(){
                $('.qr-holder').css('opacity', '0.5');
            },
            success: function(result){
                if(!result) alert('Error!');
                $('.qr-holder').css('opacity', '1');
                var data = JSON.parse(result);
                $('#settingsform-secretcode').val(data.secret);
                $('.qr-img').attr('src', data.qrUrl);
            },
            error: function(){
                alert('Error!');
            }
        });
        return false;
    }

    function createNewBackupCode(){
        $.ajax({
            url: '/auth/settings/backup-code',
            type: 'POST',
            success: function(result){
                $('#settingsform-backupcode').val(result);
            },
            error: function(){
                console.log('Error!');
            }
        });
        return false;
    }

    function submitSettings(e) {
        e.preventDefault();
        var form = $('form#auth-2fa');
        var active = $('#settingsform-active').is(':checked');
        $.ajax({
            url: '/auth/settings/submit',
            type: 'POST',
            data: form.serialize(),
            beforeSend: function(){
                $('body').css('opacity', '0.7');
                jQuery('.field-settingsform-password').removeClass('has-error');
                jQuery('.field-settingsform-password .help-block-error').html('');
            },
            success: function(result){
                $('body').css('opacity', '1');
                var data = JSON.parse(result);
                if(data.error) {
                    jQuery('.field-settingsform-password').addClass('has-error');
                    jQuery('.field-settingsform-password .help-block-error').html(data.error.password[0]);
                } else {
                    if(data.modal) {
                        $('#check-code').modal('show');
                    } else {
                        jQuery('#auth-2fa').submit();
                    }
                }
            },
            error: function(){
                alert('Error!');
            }
        });
        return false;
    }

    function checkCode() {
        var secret = $('#settingsform-secretcode').val();
        var code = $('#settingsform-checkcode').val();
        var status = $('#settingsform-active2fa').is(':checked');
        var data = 'code=' + code + '&secret=' + secret + '&status=' + status;
        $.ajax({
            url: '/auth/settings/check',
            type: 'POST',
            data: data,
            beforeSend: function(){
                $('body').css('opacity', '0.7');
            },
            success: function(result) {
                $('body').css('opacity', '1');
                if(result == 'error') {
                    jQuery('.field-loginform-checkcode').addClass('has-error');
                    jQuery('.field-loginform-checkcode .help-block-error').html('Invalid code');
                }
                if(result == 'success') {
                    //jQuery('#auth-2fa').submit();
                }
                console.log(result);
            }
        });
    }
</script>
<div class="settings container">
    <h1><?= Html::encode($this->title) ?></h1><br>

    <h4>Two Factor Authentication</h4><br>
    <?php if ( isset($msg) ) : ?>
        <?php $msg = $msg . Html::a('&times;', '#', ['class' => 'close', 'data-dismiss' => 'alert']); ?>
        <?php echo Html::tag('div', $msg, ['class' => 'alert alert-success alert-dismissable']); ?>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'auth-2fa',
        'layout' => 'horizontal',
        'options' => ['name' => 'auth-2fa'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ]
    ]); ?>

    <?= $form->field($settings, 'active', ['template' => "<label class=\"col-lg-1 control-label\" for=\"settingsform-able2fa\">{label}</label><div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",])->checkbox([
        'label' => 'Active',
    ], false) ?>

    <?= $form->field($settings, 'secretCode', ['template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<a href='#' class='btn btn-default' onclick='return createNewSecret();'>Create new secret</a>",])->textInput([
        'label' => 'Secret',
        'readonly' => 'readonly',
    ]) ?>



    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-5 qr-holder"><img src="<?= $settings['qrUrl'] ?>" class="qr-img"></div>
        <div class="col-lg-offset-1 col-lg-12" style="margin-top: 10px;"><p><i>Scan this with the Google Authenticator app.</i></p></div>
    </div>

    <?= $form->field($settings, 'backupCode', ['template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<a href='#' class='btn btn-default' onclick='return createNewBackupCode();'>Create new code</a><div class='col-lg-offset-1 col-lg-12'><p><i>If you lost your phone, you can use this code, but only once.</i></p></div><div class='col-lg-offset-1 col-lg-11 help-block help-block-error'></div>",])->textInput() ?>

    <?= $form->field($settings, 'password')->passwordInput(); ?>

    <?= $form->field($settings, 'qrUrl')->hiddenInput()->label(false); ?>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <a href="#" class="btn btn-primary" onclick="return submitSettings(event);">Save</a>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'check-google-code',
        'layout' => 'horizontal',
    ]); ?>

    <?php
    Modal::begin([
        'id' => 'check-code',
        'size' => 'modal-sm',
        'clientOptions' => [
            'backdrop' => false, 'keyboard' => true
        ]
    ]); ?>

    <?= $form->field($settings, 'checkCode', ['template' => "{label}\n<div class=\"col-lg-12\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>", 'labelOptions' => ['class' => 'col-lg-12'],])->textInput(/*['maxlength' => 6, 'type' => 'number']*/)->label('Google Authenticator code'); ?>

    <?= Html::hiddenInput('SettingsForm[secretCode]', $settings['secretCode']); ?>

    <div class="form-group">
        <div class="col-lg-12">
            <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'send-code-button', 'id' => 'send-code-button']) ?>
        </div>
    </div>

    <?php Modal::end(); ?>

    <?php ActiveForm::end(); ?>



</div>