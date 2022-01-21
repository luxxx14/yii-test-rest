<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii2rails\extension\web\helpers\ControllerHelper;
use domain\mail\v1\entities\DomainEntity;

/**
 * @var $this yii\web\View
 * @var $entity \domain\mail\v1\entities\DomainEntity
 * @var $dataProvider \yii\data\BaseDataProvider
 */

$this->title = Yii::t('mail/domain', 'title');

$baseUrl = ControllerHelper::getUrl();

$columns = [
    [
        'attribute' => 'domain',
        'label' => Yii::t('mail/domain', 'domain'),
    ],
    [
        'attribute' => 'host',
        'label' => Yii::t('mail/domain', 'host'),
    ],
    [
        'attribute' => 'port',
        'label' => Yii::t('mail/domain', 'port'),
    ],
    [
        'format' => 'html',
        'label' => Yii::t('staff/company', 'name'),
        'value' => function(DomainEntity $entity) {
            return Html::a($entity->company->name, ['/staff/company/view', 'id' => $entity->id]);
        },
    ],
];

?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{summary}{items}{pager}',
    'columns' => $columns,
]); ?>

<?= Html::a(Yii::t('action', 'create'), $baseUrl . 'create', ['class' => 'btn btn-success']) ?>
