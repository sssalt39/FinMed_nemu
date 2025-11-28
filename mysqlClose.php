<?php

/*
file:login.Auth
Author:A.N
Last updated:2025.11.21
*/

header('Content-Type: application/json');

function closeDatabase() {
    global $pdo; // グローバル変数を使用
    $pdo = null; // PDO接続を切断
}

?>