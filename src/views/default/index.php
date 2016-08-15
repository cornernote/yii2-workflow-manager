<?php
use cornernote\workflow\manager\models\Workflow;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/*
 * @var yii\web\View $this
 */

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
            'label' => '<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('workflow', 'New'),
            'url' => ['create'],
            'encode' => false,
        ],
    ];
    foreach (Workflow::find()->orderBy(['name' => SORT_ASC])->all() as $workflow) {
        /** @var Workflow $workflow */
        $items[] = [
            'label' => $workflow->name,
            'url' => ['view', 'id' => $workflow->id],
        ];
    }
    echo Nav::widget([
        'items' => $items,
        'options' => ['class' => 'nav-pills'],
    ]);
    ?>

</div>
