## アプリケーション名

-attendance-management_application

## 環境構築

Docker ビルド

・git clone git@github.com:Estra-Coachtech/laravel-docker-template.git

・docker-compose up -d --build

Laravel 開発環境構築

・docker-compose exec php bash

・composer install

・cp .env.example .env（環境変数を変更）

・composer require laravel/fortify

・php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"

・php artisan key:generate

・php artisan config:clear

・php artisan cache:clear

・php artisan optimize:clear

・php artisan tinker

・brew install node

・php artisan make:request

・php artisan make:controller

・php artisan make:migration

・php artisan migrate

・php artisan make:model

## 開発環境

・phpMyAdmin:http://localhost:8080/

## メール認証について

本アプリケーションでは、Laravel Fortify のメール認証機能を実行中（動作中）です。
メール認証が完了していないユーザーはログインできないように確認中です。

## メール送信環境

ローカル開発環境では MailHog を使用しています。

- 172.27.0.4 mailhog

- メール確認URL（実行中）：http://localhost:8025

- メール内容は実送信されています。

## 管理者ログイン情報（テスト開発用）

URL：
http://localhost/admin/login

メールアドレス：
admin@example.com

パスワード：
password123

## 使用技術

・nginx/1.21.1

・mysql Ver 8.0.26

・Laravel Framework 8.83.8

・PHP 8.1.34

・Homebrew 4.6.17

・Note v25.6.1
