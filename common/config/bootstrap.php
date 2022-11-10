<?php

use common\base\EnvHelper;

Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@public', dirname(dirname(__DIR__)) . '/public');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@modules', dirname(dirname(__DIR__)) . '/modules');
Yii::setAlias('@private_files', dirname(dirname(__DIR__)) . '/private/files');
Yii::setAlias('@files', dirname(dirname(__DIR__)) . '/public/web/files');
Yii::setAlias('@cache', dirname(dirname(__DIR__)) . '/private/caching');

// Load Env data
EnvHelper::load(dirname(__DIR__, 2));