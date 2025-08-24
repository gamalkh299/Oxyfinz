<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => '<div class="col-sm-2">{label}</div><div class="col-sm-10">{input}{error}</div>',
            'labelOptions' => ['class' => 'control-label'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">User Information</h3>
                </div>
                <div class="panel-body">
                    
                    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput([
                        'maxlength' => true,
                        'value' => '',
                        'placeholder' => $model->isNewRecord ? 'Enter password' : 'Leave blank to keep current password'
                    ]) ?>

                    <?= $form->field($model, 'password_repeat')->passwordInput([
                        'maxlength' => true,
                        'value' => '',
                        'placeholder' => 'Confirm password'
                    ]) ?>

                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Settings</h3>
                </div>
                <div class="panel-body">
                    
                    <?= $form->field($model, 'role')->dropDownList([
                        User::ROLE_USER => 'User',
                        User::ROLE_ADMIN => 'Admin'
                    ]) ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        User::STATUS_ACTIVE => 'Active',
                        User::STATUS_INACTIVE => 'Inactive'
                    ]) ?>

                    <?php if (!$model->isNewRecord): ?>
                        <div class="form-group">
                            <label class="control-label col-sm-3">Created</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <?= date('Y-m-d H:i:s', $model->created_at) ?>
                                </p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-3">Updated</label>
                            <div class="col-sm-9">
                                <p class="form-control-static">
                                    <?= date('Y-m-d H:i:s', $model->updated_at) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <?= Html::submitButton($model->isNewRecord ? 'Create User' : 'Update User', [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
$(document).ready(function() {
    // Show/hide password confirmation based on password field
    $('#user-password').on('input', function() {
        var passwordField = $('#user-password_repeat').closest('.form-group');
        if ($(this).val().length > 0) {
            passwordField.show();
            $('#user-password_repeat').attr('required', true);
        } else {
            passwordField.hide();
            $('#user-password_repeat').attr('required', false).val('');
        }
    });
    
    // Initial state
    if ($('#user-password').val().length === 0) {
        $('#user-password_repeat').closest('.form-group').hide();
    }
});
</script>