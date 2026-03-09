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

・cp .env .env.testing（テスト用の.envファイル作成）

・composer require laravel/fortify

・php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"

・php artisan key:generate

・php artisan config:clear

・php artisan cache:clear

・php artisan optimize:clear

・php artisan view:clear

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
メール認証が完了していないユーザーはログインできないように実行中です。

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

## 単体テスト実行方法

## 1.テスト用環境ファイルを用意

プロジェクト直下に`.env.testing`を作成する。

## 2.テスト用データベースを作成

CREATE DATABASE attendance_test;

## 3.テスト用マイグレーション実行

・php artisan config:clear

・php artisan migrate --env=testing

## テスト実行

php artisan test

## 実行結果例

 PASS  Tests\Unit\ExampleTest
  ✓ example

 PASS  Tests\Feature\Auth\EmailVerificationTest
  ✓ verification email is sent after registration
  ✓ verification notice page is displayed
  ✓ user is redirected to attendance page after email verification

 PASS  Tests\Feature\Auth\RegisterTest
  ✓ name is required
  ✓ email is required
  ✓ password must be at least 8 characters
  ✓ password confirmation must match
  ✓ password is required
  ✓ user can register with valid data

   PASS  Tests\Feature\Auth\AdminLoginTest
  ✓ email is required for admin login
  ✓ password is required for admin login
  ✓ admin login fails with invalid credentials

   PASS  Tests\Feature\Auth\LoginTest
  ✓ email is required for user login
  ✓ password is required for user login
  ✓ user login fails with invalid credentials

   PASS  Tests\Feature\Attendance\AttendanceStatusTest
  ✓ current datetime is displayed on attendance page
  ✓ status is off duty when no attendance record
  ✓ status is working when clocked in
  ✓ status is breaking when user is on break
  ✓ status is finished when user has clocked out

   PASS  Tests\Feature\Attendance\AttendanceStampTest
  ✓ user can clock in
  ✓ user cannot clock in twice
  ✓ clock in time is visible on attendance list
  ✓ user can start break
  ✓ user can take multiple breaks
  ✓ user can end break
  ✓ break time is visible on attendance list
  ✓ user can clock out
  ✓ clock out time is visible on attendance list

   PASS  Tests\Feature\Attendance\AttendanceListTest
  ✓ user can see all of their attendance records
  ✓ current month is displayed on attendance list
  ✓ previous month button shows previous month data
  ✓ next month button shows next month data
  ✓ user can go to attendance detail page

   PASS  Tests\Feature\Attendance\AttendanceDetailTest
  ✓ attendance detail shows logged in user name
  ✓ attendance detail shows selected date
  ✓ attendance detail shows clock in and clock out time
  ✓ attendance detail shows break time

  PASS  Tests\Feature\Attendance\AttendanceCorrectionRequestTest
  ✓ clock in cannot be after clock out
  ✓ break start cannot be after clock out
  ✓ break end cannot be after clock out
  ✓ note is required for correction request
  ✓ correction request is created
  ✓ pending request is visible on request list
  ✓ approved request is visible on request list
  ✓ user can open request detail page

## 使用技術

・nginx/1.21.1

・mysql Ver 8.0.26

・Laravel Framework 8.83.8

・PHP 8.1.34

・Homebrew 4.6.17

・Note v25.6.1
