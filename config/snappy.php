<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => 'C:\Progra~1\wkhtmltopdf\bin\wkhtmltopdf.exe',
        'timeout' => false,

        'options' => [
            'enable-local-file-access' => true,
            'encoding' => 'UTF-8',
        ],

        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTML_IMG_BINARY', '/usr/local/bin/wkhtmltoimage'),
        'timeout' => false,

        'options' => [
            'enable-local-file-access' => true,
        ],

        'env' => [],
    ],

];

