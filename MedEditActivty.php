<?php
/*
file:MedEditActivity
Author:A.N
Last updated:2025.12.10
*/

// 1. エラー返却処理読み込み
require_once('errorMsgs.php');

// 2. Input パラメータ取得
$input = json_decode(file_get_contents('php://input'), true);

$userId       = isset($input['userId'])       ? trim($input['userId'])       : null;
$userMedNo    = isset($input['userMedNo'])    ? trim($input['userMedNo'])    : null;
$medNo        = isset($input['medNo'])        ? trim($input['medNo'])        : null;
$expDate      = isset($input['expDate'])      ? trim($input['expDate'])      : null;
$remainingCnt = isset($input['remainingCnt']) ? trim($input['remainingCnt']) : null;
$medImg       = isset($input['medImg'])       ? trim($input['medImg'])       : null;

// 3. 必須チェック（userId, userMedNo は必須）
if (empty($userId))    getErrorMessage('006'); // userId 未指定
if (empty($userMedNo)) getErrorMessage('004'); // 対象データが見つかりません (No 指定なし)

// 4. DB接続
require_once('mysqlConnect.php');
global $pdo;
require_once('mysqlClose.php');

header('Content-Type: application/json');

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 5. 更新対象存在チェック
    $sqlCheck = "
        SELECT userMedNo 
        FROM userMed 
        WHERE userMedNo = :userMedNo
          AND userId = :userId
    ";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([
        ':userMedNo' => $userMedNo,
        ':userId'    => $userId
    ]);

    if ($stmtCheck->rowCount() === 0) {
        $pdo->rollBack();
        getErrorMessage('004'); // データなし
    }

    // 6. 動的 SQL（渡された項目だけ更新できる）
    $updateFields = [];
    $params = [':userMedNo' => $userMedNo, ':userId' => $userId];

    if (!is_null($medNo)) {
        $updateFields[] = "medNo = :medNo";
        $params[':medNo'] = $medNo;
    }
    if (!is_null($expDate)) {
        $updateFields[] = "expDate = :expDate";
        $params[':expDate'] = $expDate;
    }
    if (!is_null($remainingCnt)) {
        $updateFields[] = "remainingCnt = :remainingCnt";
        $params[':remainingCnt'] = $remainingCnt;
    }
    if (!is_null($medImg)) {
        $updateFields[] = "medImg = :medImg";
        $params[':medImg'] = $medImg;
    }

    // 更新項目が何もない場合
    if (count($updateFields) === 0) {
        $pdo->rollBack();
        getErrorMessage('011'); // 更新項目がありません（仮：適宜変更）
    }

    $sqlUpdate = "
        UPDATE userMed
        SET " . implode(', ', $updateFields) . "
        WHERE userMedNo = :userMedNo
          AND userId = :userId
    ";

    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute($params);

    // コミット
    $pdo->commit();

    echo json_encode([
        'result' => 'success',
        'message' => 'userMed updated successfully'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'result'           => 'error',
        'errCode'          => '001',
        'errMsg'           => 'データベース処理が異常終了しました。',
        'exceptionMessage' => $e->getMessage(),
        'exceptionCode'    => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    closeDatabase();
}
