<?php
/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\Workflow $model
 */

$this->title = Yii::t('workflow', 'Workflow') . $model->name . ', ' . Yii::t('workflow', 'Edit');
$this->params['breadcrumbs'][] = ['label' => Yii::t('workflow', 'Workflow'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('workflow', 'Workflows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('workflow', 'Update');
?>
<div class="workflow-workflow-update">

    <h1>
        <?= Yii::t('workflow', 'Workflow') ?>
        <small>
            <?= $model->name ?>
        </small>
    </h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
