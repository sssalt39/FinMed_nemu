<?php

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
if (empty($action)) {
    errorResponse('011');   // 検索区分が不正
}

// userId必須
if (empty($userId)) {
    errorResponse('005');   // ユーザIDが指定されていません
}

// DB接続
require_once 'mysqlConnect.php';
global $pdo;

// ------------------------------
// ① 薬一覧取得（action = get）
// ------------------------------
if ($action === "get") {

    try {
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
                'remaining' => $row['remaining'],
                'medImage'  => $row['medImg']
            ];
        }

        if (count($userMedList) === 0) {
            $userMedList = null;
        }

        $response = [
            'result' => 'success',
            'userMedList' => $userMedList
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
        errorResponse('001');
    }
}

// ------------------------------
// ② 残り回数減算（action = decrease）
// ------------------------------
if ($action === "decrease") {

    // userMedNo 必須
    if (empty($userMedNo)) {
        errorResponse('004'); // 対象データが見つかりません（番号として最適）
    }

    try {
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

        if ($stmt->rowCount() === 0) {
            errorResponse('002'); // 変更内容がありません
        }

        echo json_encode([
            'result' => 'success',
            'message' => 'remaining updated'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
        errorResponse('001');
    }
}

// ------------------------------
// ③ 削除（action = delete）
// ------------------------------
if ($action === "delete") {

    if (empty($userMedNo)) {
        errorResponse('004'); // 対象データが見つかりません
    }

    try {
        $sql = "
            DELETE FROM userMed
            WHERE userMedNo = :userMedNo
              AND userId = :userId
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userMedNo', $userMedNo, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            errorResponse('004'); // 対象データが無い
        }

        echo json_encode([
            'result' => 'success',
            'message' => 'deleted'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
        errorResponse('001');
    }
}

// ------------------------------
// action が不正
// ------------------------------
errorResponse('011');
?>
