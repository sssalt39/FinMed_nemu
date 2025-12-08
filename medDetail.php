<?php
/*
file:medDetail
Author:A.I
Last updated:2025.12.08
*/

// 1. エラー返却処理を読み込み
require_once 'errorMsgs.php';

// 2. Inputパラメータの取得
$input = json_decode(file_get_contents('php://input'), true);
$medNo = isset($input['medNo']) ? trim($input['medNo']) : null;

// 3. Inputパラメータの必須チェック
if (empty($medNo)) {
    getErrorMessage('013'); // 薬番号が指定されていません（仮エラーコード）
}

// 4. DB接続処理を読み込み
require_once 'mysqlConnect.php';
global $pdo;
require_once 'mysqlClose.php'; // DB切断処理

header('Content-Type: application/json');

try {
    // 5. 薬の情報を取得するSQL文
    $sql = "
        SELECT 
            medNo,
            medName,
            ageLimit,
            dosage,
            medTakeTime,
            effect,
            contraindication,
            medType
        FROM medicineInfo
        WHERE medNo = :medNo
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':medNo', $medNo, PDO::PARAM_INT);
    $stmt->execute();

    // 6. データ取得
    $medicineInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$medicineInfo) {
        // 見つからない場合
        $medicineInfo = null;
    } else {
        // 数値項目をキャスト
        $medicineInfo['medNo'] = (int)$medicineInfo['medNo'];
        $medicineInfo['ageLimit'] = (int)$medicineInfo['ageLimit'];
        $medicineInfo['medTakeTime'] = (int)$medicineInfo['medTakeTime'];
    }

    // 7. JSON返却値
    $response = [
        'result'       => 'success',
        'medicineInfo' => $medicineInfo
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

