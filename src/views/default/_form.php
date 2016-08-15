<?php

use app\models\Workflow;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var yii\web\View $this
 * @var Workflow $model
 * @var yii\widgets\ActiveForm $form
 */

?>

<div class="workflow-default-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Workflow',
        'enableClientValidation' => true,
        'errorSummaryCssClass' => 'error-summary alert alert-error'
    ]) ?>

    <?php echo $form->errorSummary($model); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> ' . ($model->isNewRecord ? Yii::t('workflow', 'Create') : Yii::t('workflow', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]) ?>
    <?= Html::a(Yii::t('workflow', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>

