<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\User;
use app\models\Expense;

/* @var $this yii\web\View */
/* @var $model app\models\Expense */

$this->title = 'Expense #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$categories = Expense::getCategories();
?>
<div class="expense-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status === Expense::STATUS_PENDING): ?>
            <?php if ($model->user_id === Yii::$app->user->id || Yii::$app->user->identity->role === User::ROLE_ADMIN): ?>
                <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>

            <?php if (Yii::$app->user->identity->role === User::ROLE_ADMIN): ?>
                <?= Html::a('Approve', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => 'Are you sure you want to approve this expense?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('Reject', ['reject', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'confirm' => 'Are you sure you want to reject this expense?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'user_id',
                'value' => $model->user->getFullName(),
                'visible' => Yii::$app->user->identity->role === User::ROLE_ADMIN,
            ],
            [
                'attribute' => 'amount',
                'format' => 'currency',
            ],
            [
                'attribute' => 'category',
                'value' => isset($categories[$model->category]) ? $categories[$model->category] : $model->category,
            ],
            'description:ntext',
            'spent_at:date',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => $model->getStatusBadge(),
            ],
            [
                'attribute' => 'receipt_path',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->receipt_path) {
                        return Html::a(
                            'View Receipt',
                            $model->receipt_path,
                            ['class' => 'btn btn-sm btn-info', 'target' => '_blank']
                        );
                    }
                    return '<span class="text-muted">No receipt uploaded</span>';
                },
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>