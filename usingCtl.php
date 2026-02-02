<?php
/*
file:usingCtl
Author:A.N
Last updated:2025.12.03

*/
// エラー返却処理
require_once 'errorMsgs.php';

// JSON取得
$input = json_decode(file_get_contents('php://input'), true);

// JSONが無効
if ($input === null) {
    errorResponse('012');
}

$action    = isset($input['action']) ? trim($input['action']) : null;
$userId    = isset($input['userId']) ? trim($input['userId']) : null;
$userMedNo = isset($input['userMedNo']) ? trim($input['userMedNo']) : null;

// action必須
if (empty($action) || $action !== 'decrease') {
    errorResponse('011'); // 検索区分が不正
}

// userId必須
if (empty($userId)) {
    errorResponse('005'); // ユーザIDが指定されていません
}

// userMedNo必須
if (empty($userMedNo)) {
    errorResponse('004'); // 対象データが見つかりません
}

// DB接続
require_once 'mysqlConnect.php';
global $pdo;

try {
    // 残り回数を1減らす
    $sql = "
        UPDATE userMed
        SET remainingCnt = remainingCnt - 1
        WHERE userMedNo = :userMedNo
          AND userId = :userId
          AND remainingCnt > 0
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userMedNo', $userMedNo, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();

    // 変更件数がない場合はエラー
    if ($stmt->rowCount() === 0) {
        errorResponse('002'); // 変更内容がありません
    }

    // 成功レスポンス
    echo json_encode([
        'result' => 'success',
        'message' => 'remainingCnt updated'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    errorResponse('001'); // データベース処理異常
}
?>
