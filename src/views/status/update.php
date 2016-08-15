<?php

/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\Status $model
 */

$this->title = Yii::t('workflow', 'Status') . $model->name . ', ' . Yii::t('workflow', 'Edit');
$this->params['breadcrumbs'][] = ['label' => Yii::t('workflow', 'Workflow'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => $model->workflow->name, 'url' => ['workflow/view', 'id' => $model->workflow->id]];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('workflow', 'Update');
?>
<div class="workflow-status-update">

    <h1>
        <?= Yii::t('workflow', 'Status') ?>
        <small>
            <?= $model->name ?>      
        </small>
    </h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
