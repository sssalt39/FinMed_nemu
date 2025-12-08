<?php
/*
file:userMedInfo
Author:A.I
Last updated:2025.12.08
*/
// 1. エラー返却処理を読み込み
require_once 'errorMsgs.php';

// 2. Inputパラメータの取得
$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['userId']) ? trim($input['userId']) : null;

// 3. Inputパラメータの必須チェック
if (empty($userId)) {
    getErrorMessage('006'); // ユーザIDが指定されていません
}

// 4. DB接続処理を読み込み
require_once 'mysqlConnect.php';
global $pdo;
require_once 'mysqlClose.php'; // DB切断処理

header('Content-Type: application/json');

try {
    // 5. お薬情報を取得するSQL文を実行
     $sql = "
        SELECT 
            um.userMedNo,
            um.userId,
            u.userName,
            um.medNo,
            mi.medName,
            um.expDate,
            mi.effect,
            um.remainingCnt AS remaining,
            um.medImg
        FROM userMed um
        JOIN user u ON um.userId = u.userId
        JOIN medicineInfo mi ON um.medNo = mi.medNo
        WHERE um.userId = :userId
        ORDER BY um.expDate ASC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
    $stmt->execute();

    // 6. データのフェッチ
    $userMedList = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userMedList[] = [
            'userMedNo' => (int)$row['userMedNo'],
            'userId'    => $row['userId'],
            'userName'  => $row['userName'],
            'medNo'     => (int)$row['medNo'],
            'medName'   => $row['medName'],
            'expDate'   => $row['expDate'],
            'effect'    => $row['effect'],
            'remaining' => $row['remaining']
        ];
    }

    if (count($userMedList) === 0) $userMedList = null;

    // 7. 返却値
    $response = [
        'result'      => 'success',
        'userMedList' => $userMedList
    ];

} catch (PDOException $e) {
    echo json_encode([
        'result'  => 'error',
        'errCode' => '001',
        'errMsg'  => 'データベース処理が異常終了しました',
        'exceptionMessage' => $e->getMessage()
    ]);
    exit;
} finally {
    closeDatabase();
}

// 8. JSON返却
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
