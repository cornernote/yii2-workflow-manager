<?php

namespace victorsemenow\workflow\manager;

use Yii;

/**
 * Class Module
 * @package victorsemenow\workflow\manager
 */
class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'victorsemenow\workflow\manager\controllers';
    /**
     * @var string
     */
    public $layout = 'main';
    
    public function init()
    {
        parent::init();
        if (!isset(Yii::$app->i18n->translations['workflow'])) {
            Yii::$app->i18n->translations['workflow'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@victorsemenow/workflow/manager/messages'
            ];
        }
    }
}
