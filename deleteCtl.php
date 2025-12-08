<?php
/*
file:deleteCtl
Author:A.N
Last updated:2025.12.03
*/
require_once 'errorMsgs.php';
require_once 'mysqlConnect.php';
global $pdo;

// JSON取得
$input = json_decode(file_get_contents('php://input'), true);

// 必須パラメータチェック
$userId    = isset($input['userId']) ? trim($input['userId']) : null;
$userMedNo = isset($input['userMedNo']) ? (int)$input['userMedNo'] : null;

if (empty($userId)) errorResponse('005');     // ユーザID未指定
if (empty($userMedNo)) errorResponse('004');  // 削除対象番号未指定

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // -------------------------------
    // ① 指定レコードを削除
    // -------------------------------
    $stmtDelete = $pdo->prepare("
        DELETE FROM userMed
        WHERE userMedNo = :userMedNo
          AND userId = :userId
    ");
    $stmtDelete->execute([
        ':userMedNo' => $userMedNo,
        ':userId'    => $userId
    ]);

    // 削除対象なしの場合はエラー
    if ($stmtDelete->rowCount() === 0) {
        $pdo->rollBack();
        errorResponse('004');
    }

    // -------------------------------
    // ② userMedNo を一時的に負の値に変更して重複回避
    // -------------------------------
    $stmtNeg = $pdo->prepare("
        UPDATE userMed
        SET userMedNo = -userMedNo
        WHERE userId = :userId
    ");
    $stmtNeg->execute([':userId' => $userId]);

    // -------------------------------
    // ③ 連番を再設定（expDate順で並び替え）
    // -------------------------------
    // 変数 @newNo を初期化
    $pdo->exec("SET @newNo = 0");

    $stmtReseq = $pdo->prepare("
        UPDATE userMed
        SET userMedNo = (@newNo := @newNo + 1)
        WHERE userId = :userId
        ORDER BY expDate ASC
    ");
    $stmtReseq->execute([':userId' => $userId]);

    // トランザクション確定
    $pdo->commit();

    // 成功レスポンス
    echo json_encode([
        'result'  => 'success',
        'message' => 'deleted and userMedNo resequenced'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    // トランザクション中ならロールバック
    if ($pdo->inTransaction()) $pdo->rollBack();

    // 詳細なエラー情報を返す
    echo json_encode([
        'result'           => 'error',
        'errCode'          => '001',
        'errMsg'           => 'データベース処理が異常終了しました。',
        'exceptionMessage' => $e->getMessage(),
        'exceptionCode'    => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}






?>
