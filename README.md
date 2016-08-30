# Yii2 Workflow Manager

[![Latest Version](https://img.shields.io/github/tag/cornernote/yii2-workflow-manager.svg?style=flat-square&label=release)](https://github.com/cornernote/yii2-workflow-manager/tags)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/cornernote/yii2-workflow-manager/master.svg?style=flat-square)](https://travis-ci.org/cornernote/yii2-workflow-manager)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/cornernote/yii2-workflow-manager.svg?style=flat-square)](https://scrutinizer-ci.com/g/cornernote/yii2-workflow-manager/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/cornernote/yii2-workflow-manager.svg?style=flat-square)](https://scrutinizer-ci.com/g/cornernote/yii2-workflow-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/cornernote/yii2-workflow-manager.svg?style=flat-square)](https://packagist.org/packages/cornernote/yii2-workflow-manager)

Workflow Manager for Yii2. Extends [Yii2-Workflow](https://github.com/raoul2000/yii2-workflow/) to provide an interface to manage workflows.

![screenshot](https://cloud.githubusercontent.com/assets/51875/17660161/a351c124-6316-11e6-8e2b-28340fe6dc8d.png)


## Features

* Create and manage workflows, statuses and transitions using a simple interface.
* Manage metadata for each status to allow additional data such as colors and icons.
* Displays the workflow transitions using [Yii2 Workflow View](https://github.com/raoul2000/yii2-workflow-view)


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ composer require cornernote/yii2-workflow-manager "*"
```

or add

```
"cornernote/yii2-workflow-manager": "*"
```

to the `require` section of your `composer.json` file.


## Migrations

```
$ php yii migrate --migrationPath=@cornernote/workflow/manager/migrations
```


## Configuration

```php
$config = [
    'components' => [
        'workflowSource' => [
            'class' => 'cornernote\workflow\manager\components\WorkflowDbSource',
        ],
    ],
    'modules' => [
        'workflow' => [
            'class' => 'cornernote\workflow\manager\Module',
        ],
    ],
];
```


## Usage

Simply visit `?r=workflow` within your application to start managing workflows.

Once you have defined a workflow, you can attach it to a model as follows:

```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
                'defaultWorkflowId' => 'post',
                'propagateErrorsToModel' => true,
            ],
        ];
    }
}
```


## License

- Author: Brett O'Donnell <cornernote@gmail.com>
- Source Code: https://github.com/cornernote/yii2-workflow-manager
- Copyright © 2016 Mr PHP <info@mrphp.com.au>
- License: BSD-3-Clause https://raw.github.com/cornernote/yii2-workflow-manager/master/LICENSE


## Links

- [Yii2 Extension](http://www.yiiframework.com/extension/yii2-workflow-manager)
- [Composer Package](https://packagist.org/packages/cornernote/yii2-workflow-manager)
- [MrPHP](http://mrphp.com.au)


[![Mr PHP](https://raw.github.com/cornernote/mrphp-assets/master/img/code-banner.png)](http://mrphp.com.au) 
