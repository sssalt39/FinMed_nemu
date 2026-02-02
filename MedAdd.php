<?php
/*
file:MedAdd
Author:A.N
Last updated:2025.12.10
*/

// 1. エラー返却処理を読み込み
require_once 'errorMsgs.php';

// 2. Input パラメータの取得
$input = json_decode(file_get_contents('php://input'), true);

$userId       = isset($input['userId']) ? trim($input['userId']) : null;
$medNo        = isset($input['medNo']) ? trim($input['medNo']) : null;
$expDate      = isset($input['expDate']) ? trim($input['expDate']) : null;
$remainingCnt = isset($input['remainingCnt']) ? trim($input['remainingCnt']) : null;
$medImg       = isset($input['medImg']) ? trim($input['medImg']) : null;

// 3. 必須チェック
if (empty($userId)) {
    getErrorMessage('006'); // userId 未指定
}
if (empty($medNo)) {
    getErrorMessage('018'); // 薬が指定されていません（仮のエラー）
}
if (empty($expDate)) {
    getErrorMessage('019'); // 有効期限が指定されていません（仮のエラー）
}
if ($remainingCnt === null || $remainingCnt === '') {
    getErrorMessage('020'); // 残数が指定されていません（仮のエラー）
}

// 4. DB接続
require_once 'mysqlConnect.php';
global $pdo;
require_once 'mysqlClose.php';

header('Content-Type: application/json');

try {
    // 5. INSERT 文作成
    $sql = "
        INSERT INTO userMed(
            userId,
            medNo,
            expDate,
            remainingCnt,
            medImg
        )
        VALUES (
            :userId,
            :medNo,
            :expDate,
            :remainingCnt,
            :medImg
        )
    ";

    $stmt = $pdo->prepare($sql);

    // 6. バインド & 実行
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->bindParam(':medNo', $medNo, PDO::PARAM_INT);
    $stmt->bindParam(':expDate', $expDate, PDO::PARAM_STR);
    $stmt->bindParam(':remainingCnt', $remainingCnt, PDO::PARAM_INT);
    $stmt->bindParam(':medImg', $medImg, PDO::PARAM_STR);

    $stmt->execute();

    // 登録したデータの userMedNo を返す
    $insertedId = $pdo->lastInsertId();

    $response = [
        'result' => 'success',
        'userMedNo' => (int)$insertedId
    ];

} catch(PDOException $e) {
    echo json_encode([
        'result' => 'error',
        'errCode' => '001',
        'errMsg' => 'データベース処理が異常終了しました。',
        'exceptionMessage' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    closeDatabase();
}

// 7. JSON返却
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

