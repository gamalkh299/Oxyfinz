<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Expense;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Expense */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="expense-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'category')->dropDownList(Expense::getCategories()) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'spent_at')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'receiptFile')->fileInput(['class' => 'form-control']) ?>
            <?php if (!$model->isNewRecord && $model->receipt_path): ?>
                <div class="help-block">
                    Current receipt: <?= Html::a('View', $model->receipt_path, ['target' => '_blank']) ?>
                    <br>
                    <small class="text-muted">Upload a new file to replace the current one.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>