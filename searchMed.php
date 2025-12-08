<?php
/*
file:searchMed
Author:A.N
Last updated:2025.12.08
*/

//1.エラー返却処理を読み込み
require_once('errorMsgs.php');

//2.input パラメータの取得
$input = json_decode(file_get_contents('php://input'), true);
$string = isset($input['string']) ? trim($input['string']) : null; //検索文字列

//3.input パラメータの必須チェック
if (empty($string)) {
    getErrorMessage('010'); // 点検文字列が指定されていません
}

//5.DB接続処理を読み込み
require_once('mysqlConnect.php');
global $pdo;
require_once('mysqlClose.php');

header('Content-Type: application/json');

try {
    // 薬の名前部分一致検索
    $sql = "
        SELECT 
            medNo,
            medName
        FROM medicineInfo
        WHERE medName LIKE :string
        ORDER BY medName ASC
    ";
    $stmt = $pdo->prepare($sql);
    $likeString = '%' . $string . '%';
    $stmt->bindParam(':string', $likeString, PDO::PARAM_STR);
    $stmt->execute();

    $medList = [];
    while ($row = $stmt->fetch()) {
        $medList[] = [
            'medNo'   => (int)$row['medNo'],
            'medName' => $row['medName']
        ];
    }

    if (count($medList) === 0) {
        $medList = null;
    }

    $response = [
        'result'  => 'success',
        'medList' => $medList
    ];

} catch (PDOException $e) {
    echo json_encode([
        'result'          => 'error',
        'errCode'         => '001',
        'errMsg'          => 'データベース処理が異常終了しました。',
        'exceptionMessage'=> $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    closeDatabase();
}

// 正常終了レスポンス
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
