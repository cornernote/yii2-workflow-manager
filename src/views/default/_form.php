<?php
/**
 * @var yii\web\View $this
 * @var victorsemenow\workflow\manager\models\Workflow $model
 * @var ActiveForm $form
 */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kartik\icons\Icon;
?>

<div class="workflow-default-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Workflow',
        'enableClientValidation' => false,
        'errorSummaryCssClass' => 'error-summary alert alert-error'
    ]) ?>

    <?php echo $form->errorSummary($model); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= Html::submitButton(Icon::show('check') . ($model->isNewRecord ? Yii::t('workflow', 'Create') : Yii::t('workflow', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]) ?>
    <?= Html::a(Yii::t('workflow', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>

