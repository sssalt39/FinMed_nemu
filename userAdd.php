<?php
/*
file:login.Auth
Author:A.N
Last updated:2025.11.21
*/

// 1.エラー返却処理を読み込み
require_once 'errorMsgs.php';

// 2.input パラメータの取得
$input = json_decode(file_get_contents('php://input'), true);

$userId   = isset($input['userId'])   ? trim($input['userId'])   : null;
$userName = isset($input['userName']) ? trim($input['userName']) : null;
$password = isset($input['password']) ? trim($input['password']) : null;
$role     = isset($input['role'])     ? trim($input['role'])     : null;



// 3.必須チェック
if (empty($userId)) {
    errorResponse("005"); // ユーザIDが指定されていません
}
if (empty($userName)) {
    errorResponse("009"); // ユーザ名が指定されていません
}
if (empty($password)) {
    errorResponse("006"); // パスワードが指定されていません
}
if (empty($role)) {
    errorResponse("010"); // 権限IDが指定されていません
}

// role の値チェック
if (!in_array($role, ['user','admin'])) {
    errorResponse("007"); // 権限がありません
}



// 4. DB接続
require_once 'mysqlConnect.php';
global $pdo;
require_once 'mysqlClose.php'; // DB切断処理を読み込み



header('Content-Type: application/json');



try {
   

    // 5.トランザクション開始
    $pdo->beginTransaction();

    // 6.ユーザ登録
    $stmt = $pdo->prepare("
        INSERT INTO user (userId, userName, password, role)
        VALUES (:userId, :userName, :password, :role)
    ");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':role', $role, PDO::PARAM_STR);

   

    $stmt->execute();

    

    // 7.コミット
    $pdo->commit();

    // 8.返却値
    $response = [
        'result' => 'success',
        'role'   => $role
    ];

} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'errorCode' => '001',
        'errorMessage' => 'データベース処理が異常終了しました',
        'exceptionMessage' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    closeDatabase();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>
