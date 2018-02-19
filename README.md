Two-Factor Authentication
=========================
Two-Factor Authentication

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require devmary/yii2-auth:@dev
```

or add

```
"devmary/yii2-auth": "*"
```

to the require section of your `composer.json` file.


Configure
---------

Add following lines to your main configuration file:

```php
'modules' => [
    'auth' => [
        'class' => 'devmary\auth\Module',
    ],
],
```

Update database schema
----------------------

The last thing you need to do is updating your database schema by applying the
migrations. Make sure that you have properly configured `db` application component
and run the following command:

```bash
$ php yii migrate/up --migrationPath=@vendor/devmary/yii2-auth/migrations
```



Usage
-----

Once the extension is installed, add action to your login form:

```php
$form = ActiveForm::begin([
    ...
    'action' => ['auth/login/ajax-login'],
    ...
]);
```

and use following code in your login view file:

```php
    <?php $form = ActiveForm::begin([
        'id' => 'check-google-code',
        'layout' => 'horizontal',
        'action' => ['auth/login/ajax-login'],
    ]); ?>

    <?php yii\bootstrap\Modal::begin([
        'id' => 'check-code',
        'size' => 'modal-sm',
        'clientOptions' => [
            'backdrop' => false, 'keyboard' => true
        ]
    ]); ?>

    <div class="form-group field-loginform-checkcode">
        <label class="col-lg-12" for="loginform-checkcode">Google Authenticator code</label>
        <div class="col-lg-12"><?= Html::textInput('LoginForm[checkCode]', '', ['class' => 'form-control', 'id' => 'loginform-checkcode']); ?></div>
        <div class="col-lg-12"><div class="help-block help-block-error "></div></div>
    </div>

    <div class="form-group">
        <div class="col-lg-12">
            <?= Html::submitButton('Send', ['class' => 'btn btn-primary', 'name' => 'send-code-button', 'id' => 'send-code-button']) ?>
        </div>
    </div>

    <?php yii\bootstrap\Modal::end(); ?>

    <?php ActiveForm::end(); ?>
```

and

```php
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
$('form#login-form').on('submit', function(e){
    e.preventDefault();
    var form = $(this);
    $.ajax({
        url    : form.attr('action'),
        type   : 'post',
        data   : form.serialize(),
        beforeSend: function(){
            $('body').css('opacity', '0.7');
        },
        success: function (response) {
            $('body').css('opacity', '1');
            var data = JSON.parse(response);
            if (data.success) {

            } else {
                var errorMsg = data.data['loginform-password'][0];
                jQuery('.field-loginform-password').addClass('has-error');
                jQuery('.field-loginform-password .help-block-error').html(errorMsg);
                console.log(data.data);
            }

            if(data.modal){
                jQuery('#check-code').modal();
            }
        },
    });
});
jQuery('#check-google-code').submit(function(e){
    e.preventDefault();
    var loginForm = $('form#login-form');
    console.log('send code');
        var code = $('#loginform-checkcode').val();
        var data = 'code=' + code + '&loginform=' + loginForm.serialize();
        $.ajax({
            url: '/auth/login/check-code',
            type: 'POST',
            data: data,
            beforeSend: function(){
                $('body').css('opacity', '0.7');
                jQuery('.field-loginform-checkcode').removeClass('has-error');
                jQuery('.field-loginform-checkcode .help-block-error').html('')
            },
            success: function(result) {
                $('body').css('opacity', '1');
                if(result == 'error') {
                    jQuery('.field-loginform-checkcode').addClass('has-error');
                    jQuery('.field-loginform-checkcode .help-block-error').html('Invalid code');
                }
                if(result == 'success') {
                    jQuery('#auth-2fa').submit();
                }
                console.log(result);
            }
        });
});

JS;

$this->registerJs($script);
```
