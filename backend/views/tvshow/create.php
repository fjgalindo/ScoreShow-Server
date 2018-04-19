<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Tvshow */

$this->title = Yii::t('app', 'Create Tvshow');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tvshows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tvshow-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
