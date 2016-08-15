<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\Workflow $model
 */

$this->title = Yii::t('workflow', 'Create Workflow');
$this->params['breadcrumbs'][] = ['label' => Yii::t('workflow', 'Workflow'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workflow-default-create">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
