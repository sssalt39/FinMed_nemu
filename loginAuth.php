<?php
/*
file:login.Auth
Author:A.N
Last updated:2025.11.21
*/

require_once 'errorMsgs.php';    // エラー返却処理
require_once 'mysqlConnect.php'; // DB接続処理
require_once 'mysqlClose.php';   // DB切断処理

header('Content-Type: application/json');

// １．POSTメソッドチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "result"  => "error",
        "message" => "このAPIはPOSTメソッドでアクセスしてください。"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ２．入力パラメータ取得（JSON対応）
$postData = json_decode(file_get_contents('php://input'), true);
$userId   = isset($postData['userId']) ? trim($postData['userId']) : null;
$password = isset($postData['password']) ? trim($postData['password']) : null;

// ３．必須チェック
if (!$userId) {
    errorResponse("005"); // ユーザID未指定
}
if (!$password) {
    errorResponse("006"); // パスワード未指定
}

try {
    // ４．ユーザ情報取得
    $sql  = "SELECT userId, password, role FROM user WHERE userId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ５．認証チェック
    if (!$user || $user['password'] !== $password) {
        errorResponse("003"); // ユーザIDまたはパスワードが違います
    }

    // ６．ログイン成功レスポンス
    $response = [
        "result" => "success",
        "role"   => $user['role'] // admin / user
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    errorResponse("001"); // DB処理異常
} finally {
    require_once 'mysqlClose.php'; // DB切断
}
?>