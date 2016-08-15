<?php
/**
 * @var yii\web\View $this
 * @var cornernote\workflow\manager\models\form\StatusForm $model
 * @var cornernote\workflow\manager\models\Metadata $metadata
 * @var ActiveForm $form
 * @var string $key
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<td>
    <?= $form->field($metadata, 'key')->textInput([
        'id' => "Metadatas_{$key}_key",
        'name' => "Metadatas[$key][key]",
    ])->label(false) ?>
</td>
<td>
    <?= $form->field($metadata, 'value')->textInput([
        'id' => "Metadatas_{$key}_value",
        'name' => "Metadatas[$key][value]",
    ])->label(false) ?>
</td>
<td>
    <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', 'javascript:void(0);', [
        'class' => 'status-remove-metadata-button btn btn-default btn-xs',
        'title' => Yii::t('workflow', 'Remove {key}', ['key' => $key]),
    ]) ?>
</td>