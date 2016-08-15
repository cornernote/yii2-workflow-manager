<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\Status $model
 * @var yii\widgets\ActiveForm $form
 */

?>

<div class="workflow-status-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Status',
        'enableClientValidation' => true,
        'errorSummaryCssClass' => 'error-summary alert alert-error'
    ]);
    ?>

    <?php echo $form->errorSummary($model); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>

    <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> ' . ($model->isNewRecord ? Yii::t('workflow', 'Create') : Yii::t('workflow', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]) ?>
    <?= Html::a(Yii::t('workflow', 'Cancel'), ['default/view', 'id' => $model->workflow_id], ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>

