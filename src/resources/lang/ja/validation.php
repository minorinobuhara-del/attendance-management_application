<?php

return [
    'required' => ':attributeを入力してください',

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => 'お名前',
    ],

    'custom' => [
        'email' => [
            'required' => 'メールアドレスを入力してください',
        ],
        'password' => [
            'required' => 'パスワードを入力してください',
        ],
    ],
];