<?php

/**
 * 在config目录下创建databses文件夹，分别创建web/index.php YII_ENV常量名的db配置文件，依据不同环境引入不同数据库配置文件
 */
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=host;dbname=paycenter',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
