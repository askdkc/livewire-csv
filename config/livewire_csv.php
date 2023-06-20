<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Layout
    |--------------------------------------------------------------------------
    |
    | This package plans on supporting multiple CSS frameworks.
    | Currently, 'tailwindcss' is the default and only supported framework.
    |
    */
    'layout' => 'tailwindcss',

    /*
    |--------------------------------------------------------------------------
    | Default File Type
    |--------------------------------------------------------------------------
    |
    | If you change file_type to tsv, it can handle tsv files.
    |
    */
    'file_type' => 'csv',

    /*
    |--------------------------------------------------------------------------
    | Default Set Delimiter
    |--------------------------------------------------------------------------
    |
    | If you change Set Delimiter to file.
    |
    */
    'set_delimiter' => ',',


    /*
    |--------------------------------------------------------------------------
    | Max Upload File Size
    |--------------------------------------------------------------------------
    |
    | The default maximumum file size that can be imported by this
    | package is 100MB. If you wish to increase/decrease this value,
    | change the value in KB below.
    |
    */
    'file_upload_size' => 102400,
];
