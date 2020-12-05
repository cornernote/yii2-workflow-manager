<?php
/**
 * @var $this \yii\web\View
 * @var $content string
 */

use cornernote\workflow\manager\models\Workflow;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Html;
use yii\bootstrap4\Breadcrumbs;
use kartik\icons\FontAwesomeAsset;




?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php // $this->registerCss('body{padding-top: 60px; padding-bottom: 60px;}'); ?>
    <?php FontAwesomeAsset::register($this) ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
NavBar::begin([
    'brandLabel' => Yii::t('workflow', 'Workflow'),
    'brandUrl' => ['default/index'],
    'options' => ['class' => 'navbar navbar-expand-lg navbar-light bg-light'],
    'innerContainerOptions' => ['class' => 'container-fluid'],
]);
$items = [];
foreach (Workflow::find()->orderBy(['id' => SORT_ASC])->all() as $workflow) {
    /** @var Workflow $workflow */
    $items[] = [
        'label' => $workflow->id,
        'url' => ['default/view', 'id' => $workflow->id],
    ];
}
echo Nav::widget([
    'items' => $items,
    'options' => ['class' => 'navbar-nav mr-auto'],
]);
echo Nav::widget([
    'items' => [
        ['label' => Yii::$app->name, 'url' => Yii::$app->getHomeUrl()],
    ],
    'options' => ['class' => 'navbar-nav navbar-right'],
]);
NavBar::end();
?>

<div class="container-fluid">
    <?php if (isset($this->params['breadcrumbs'])) { ?>
        <div class="breadcrumbs">
            <?= Breadcrumbs::widget([
                'links' => $this->params['breadcrumbs'],
            ]) ?>
        </div>
    <?php } ?>

    <?= $content ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
