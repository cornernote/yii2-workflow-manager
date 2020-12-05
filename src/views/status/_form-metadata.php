<?php
/**
 * @var yii\web\View $this
 * @var victorsemenow\workflow\manager\models\form\StatusForm $model
 * @var victorsemenow\workflow\manager\models\Metadata $metadata
 * @var ActiveForm $form
 * @var string $key
 */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use kartik\icons\Icon;

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
    <?= Html::a(Icon::show('times'), 'javascript:void(0);', [
        'class' => 'status-remove-metadata-button btn btn-light btn-xs',
        'title' => Yii::t('workflow', 'Remove {key}', ['key' => $key]),
    ]) ?>
</td>