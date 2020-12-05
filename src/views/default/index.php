<?php
/*
 * @var yii\web\View $this
 */

use victorsemenow\workflow\manager\models\Workflow;
use yii\bootstrap4\Nav;
use yii\bootstrap4\Html;
use kartik\icons\Icon;

$this->title = Yii::t('workflow', 'Workflow');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workflow-default-index">

    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php
    $items = [
        [
            'label' => Icon::show('plus').' ' . Yii::t('workflow', 'Create'),
            'url' => ['create'],
            'encode' => false,
        ],
    ];
    foreach (Workflow::find()->orderBy(['id' => SORT_ASC])->all() as $workflow) {
        /** @var Workflow $workflow */
        $items[] = [
            'label' => $workflow->title.' ('.$workflow->id.')',
            'url' => ['view', 'id' => $workflow->id],
            'linkOptions' => ['style' => 'color:#fff;background:' . $workflow->getColor()],
        ];
    }
    echo Nav::widget([
        'items' => $items,
        'options' => ['class' => 'nav nav-pills'],
    ]);
    ?>

</div>
