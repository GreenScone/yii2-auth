<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $settings app\modules\auth\models\SettingsForm */

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
    console.log('send code');
    var form = $(this);
    var secret = $('#modalform-secretcode').val();
    var backup_code = $('#settingsform-backupcode').val();
        //var code = $('#settingsform-checkcode').val();
        var status = $('#settingsform-active').is(':checked');
        //var data = 'code=' + code + '&secret=' + secret + '&status=' + status;
        $.ajax({
            url: '/auth/settings/check',
            type: 'POST',
            data: 'status=' + status + '&backup_code='+ backup_code + '&' + form.serialize(),
            beforeSend: function(){
                //$('body').css('opacity', '0.7');
                jQuery('.field-settingsform-checkcode').removeClass('has-error');
                jQuery('.field-settingsform-checkcode .help-block-error').html('')
            },
            success: function(result) {
                //$('body').css('opacity', '1');
                var data = JSON.parse(result);
                if(data.error) {
                    //$('body').css('opacity', '1');
                    jQuery('.field-settingsform-checkcode').addClass('has-error');
                    jQuery('.field-settingsform-checkcode .help-block-error').html(data.error.checkcode[0]);
                } else {
                    jQuery('#auth-2fa').submit();
                }
                /*if(result == 'success') {
                    jQuery('#auth-2fa').submit();
                }*/
                console.log(result);
            }
        });
});

//jQuery('form#auth-2fa').submit(function(e){
//    e.preventDefault();
//    var form = $(this);
//    var active_2fa = $('#settingsform-active2fa').is(':checked');
//        var secret = $('#settingsform-secretcode').val();
//        var imgUrl = $('#settingsform-qrurl').val();
//        var data = 'active=' + active_2fa + '&secret=' + secret + '&imgurl=' + imgUrl;
//
//        $.ajax({
//            url: '/auth/settings/submit',
//            type: 'POST',
//            data: form.serialize(),
//            beforeSend: function(){
//                jQuery('.field-settingsform-password').removeClass('has-error');
//                jQuery('.field-settingsform-password .help-block-error').html('');
//            },
//            success: function(result){
//                console.log(result);
//                var data = JSON.parse(result);
//                /*if (result) {
//                    $('#check-code').modal('show');
//                }*/
//                console.log(data);
//                if(data.error) {
//                console.log(data.error.password[0]);
//                    jQuery('.field-settingsform-password').addClass('has-error');
//                    jQuery('.field-settingsform-password .help-block-error').html('Invalid code');
//                } else {
//                    $('#check-code').modal('show');
//                }
//            },
//            error: function(){
//                alert('Error!');
//            }
//        });
//        return false;
//});

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
        var secret = $('#settingsform-secretcode').val();
        var imgUrl = $('#settingsform-qrurl').val();
        //var imgUrl = $('.qr-img').attr('src');

        var data = 'active=' + active + '&secret=' + secret + '&imgurl=' + imgUrl;


        /*var formData = new FormData('auth-2fa');
        var form_data = document.getElementById("auth-2fa");
        var data = "";
        var i;
        for (i = 0; i < form_data.length; i++) {
            data = data + form_data.elements[i].name + '=' + form_data.elements[i].value + "&";
        }
        console.log(formData);*/
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
                console.log(result);
                var data = JSON.parse(result);
                /*if (result) {
                 $('#check-code').modal('show');
                 }*/
                //console.log(data);
                if(data.error) {
                    //console.log(data.error.password[0]);
                    jQuery('.field-settingsform-password').addClass('has-error');
                    jQuery('.field-settingsform-password .help-block-error').html(data.error.password[0]);
                } else {
                    if(data.modal) {
                        $('#check-code').modal('show');
                    } else {
                        jQuery('#auth-2fa').submit();
                    }
                }
                //var data = JSON.parse(result);
                //console.log(data);
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

    /*jQuery(document).ready(function(){
        jQuery('#auth-2fa').submit(function(e){
            e.preventDefault();
            var formData = new FormData();
            $.ajax({
                url: '/auth/settings/submit',
                type: 'POST',
                data: formData,
                success: function(result){
                    var data = JSON.parse(result);
                    console.log(data);
                },
                error: function(){
                    alert('Error!');
                }
            });
            return false;
        });
    });
*/
</script>
<div class="settings container">
    <h1><?= Html::encode($this->title) ?></h1><br>

    <h4>Two Factor Authentication</h4><br>
    <?php if ( isset($msg) ) : ?>
        <?php $msg = $msg . Html::a('&times;', '#', ['class' => 'close', 'data-dismiss' => 'alert']); ?>
        <?php echo Html::tag('div', $msg, ['class' => 'alert alert-success alert-dismissable']); ?>
    <?php endif; ?>

    <?php
//    Pjax::begin([
//    // Pjax options
//    ]);
    ?>

    <?php $form = ActiveForm::begin([
        'id' => 'auth-2fa',
        'layout' => 'horizontal',
        //'options' => ['data' => ['pjax' => true]],
        'options' => ['name' => 'auth-2fa'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ]
    ]); ?>

    <?= $form->field($settings, 'active', ['template' => "<label class=\"col-lg-1 control-label\" for=\"settingsform-able2fa\">{label}</label><div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",])->checkbox([
        //'autofocus' => true,
        'label' => 'Active',
//        'template' => "<label class=\"col-lg-1 control-label\" for=\"settingsform-able2fa\">{label}</label><div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ], false) ?>

    <?= $form->field($settings, 'secretCode', ['template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<a href='#' class='btn btn-default' onclick='return createNewSecret();'>Create new secret</a>",])->textInput([
        'label' => 'Secret',
        'readonly' => 'readonly',
        //'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div><a href='#' class='btn btn-primary'></a>",
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

    <?php //Pjax::end(); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'check-google-code',
        'layout' => 'horizontal',
        /*'options' => ['name' => 'auth-2fa'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ]*/
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

    <?= $form->field($settings, 'secretCode', ['template' => "{input}"])->hiddenInput(['id' => 'modalform-secretcode'])->label(false); ?>
    <!--<div>
        <a href="#" class="btn btn-primary" onclick="checkCode();">Send</a>
    </div>-->
    <div class="form-group">
        <div class="col-lg-12">
            <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'send-code-button', 'id' => 'send-code-button']) ?>
        </div>
    </div>

    <?php Modal::end(); ?>

    <?php ActiveForm::end(); ?>



</div>