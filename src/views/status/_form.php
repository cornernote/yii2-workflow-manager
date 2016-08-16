<?php
/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\form\StatusForm $model
 * @var ActiveForm $form
 */

use cornernote\workflow\manager\models\Metadata;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="workflow-status-form">

    <?php $form = ActiveForm::begin([
        'id' => 'Status',
        'enableClientValidation' => false,
        'errorSummaryCssClass' => 'error-summary alert alert-error'
    ]) ?>

    <?php echo $model->errorSummary($form); ?>

    <fieldset>
        <legend><?= Yii::t('workflow', 'Status') ?></legend>
        <?= $form->field($model->status, 'id')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model->status, 'label')->textInput(['maxlength' => true]) ?>
    </fieldset>

    <fieldset>
        <legend><?= Yii::t('workflow', 'Metadata') ?>
            <?php
            // new metadata button
            echo Html::a('<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('workflow', 'New Metadata'), 'javascript:void(0);', [
                'id' => 'status-new-metadata-button',
                'class' => 'pull-right btn btn-default btn-xs'
            ])
            ?>
        </legend>
        <?php
        // metadata table
        $metadata = new Metadata();
        $metadata->loadDefaultValues();
        echo '<table id="status-metadatas" class="table table-condensed table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . $metadata->getAttributeLabel('key') . '</th>';
        echo '<th>' . $metadata->getAttributeLabel('value') . '</th>';
        echo '<td>&nbsp;</td>';
        echo '</tr>';
        echo '</thead>';
        echo '</tbody>';
        // existing metadatas fields
        foreach ($model->metadatas as $key => $_metadata) {
            echo '<tr>';
            echo $this->render('_form-metadata', [
                'key' => $_metadata->isNewRecord ? (strpos($key, 'new') !== false ? $key : 'new' . $key) : $_metadata->key,
                'form' => $form,
                'metadata' => $_metadata,
            ]);
            echo '</tr>';
        }
        // new metadata fields
        echo '<tr id="status-new-metadata-block" style="display: none;">';
        echo $this->render('_form-metadata', [
            'key' => '__id__',
            'form' => $form,
            'metadata' => $metadata,
        ]);
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        ?>

        <?php ob_start(); // output buffer the javascript to register later ?>
        <script>
            // add metadata button
            var metadata_k = <?php echo isset($key) ? str_replace('new', '', $key) : 0; ?>;
            $('#status-new-metadata-button').on('click', function () {
                metadata_k += 1;
                $('#status-metadatas').find('tbody')
                    .append('<tr>' + $('#status-new-metadata-block').html().replace(/__id__/g, 'new' + metadata_k) + '</tr>');
            });
            // remove metadata button
            $(document).on('click', '.status-remove-metadata-button', function () {
                $(this).closest('tbody tr').remove();
            });
        </script>
        <?php $this->registerJs(str_replace(['<script>', '</script>'], '', ob_get_clean())); ?>

    </fieldset>

    <?= Html::submitButton('<span class="glyphicon glyphicon-check"></span> ' . ($model->status->isNewRecord ? Yii::t('workflow', 'Create') : Yii::t('workflow', 'Save')), [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
    ]) ?>
    <?= Html::a(Yii::t('workflow', 'Cancel'), ['default/view', 'id' => $model->status->workflow_id], ['class' => 'btn btn-default']) ?>

    <?php ActiveForm::end(); ?>

</div>

