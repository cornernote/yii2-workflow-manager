<?php

/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\Status $model
 */

use yii\helpers\Html;

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('workflow', 'Workflow'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => $model->workflow->id, 'url' => ['default/view', 'id' => $model->workflow->id]];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="workflow-status-update">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
