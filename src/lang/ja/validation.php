<?php

return [
    'path_id' => ':attribute は無効なID形式です。（正の整数のみ許可）',
    // デフォルトのバリデーションメッセージ
    'required' => ':attribute は必須です。',
    'string' => ':attribute は文字列でなければなりません。',
    'max' => [
        'string' => ':attribute は :max 文字以下でなければなりません。',
    ],
    'integer' => ':attribute は整数でなければなりません。',
    'exists' => '選択された :attribute は無効です。',

    // 属性名の翻訳
    'attributes' => [
        'id' => 'ID',
        'title' => 'タイトル',
        'content' => '内容',
    ], ];
