<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            [
                'attribute' => 'first_name',
                'value' => function ($model) {
                    return $model->getFullName();
                },
                'label' => 'Full Name',
            ],
            'email:email',
            [
                'attribute' => 'role',
                'value' => function ($model) {
                    return ucfirst($model->role);
                },
                'filter' => [
                    User::ROLE_USER => 'User',
                    User::ROLE_ADMIN => 'Admin'
                ],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $class = $model->status ? 'label-success' : 'label-danger';
                    $text = $model->status ? 'Active' : 'Inactive';
                    return "<span class='label $class'>$text</span>";
                },
                'filter' => [
                    User::STATUS_ACTIVE => 'Active',
                    User::STATUS_INACTIVE => 'Inactive'
                ],
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s'],
                'value' => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                },
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span> View', $url, [
                            'title' => 'View',
                            'class' => 'btn btn-sm btn-primary'
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span> Update', $url, [
                            'title' => 'Update',
                            'class' => 'btn btn-sm btn-info'
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> Delete', $url, [
                            'title' => 'Delete',
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this user?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>