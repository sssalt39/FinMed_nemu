<?php

/*
file:login.Auth
Author:A.N
Last updated:2025.11.21
*/

// １．エラーメッセージを取得するメソッドを作成する。（引数：エラーコード）
function getErrorMessage($errorCode) {
    $errors = [
        "001" => "データベース処理が異常終了しました。",
        "002" => "変更内容がありません。",
        "003" => "ユーザIDまたはパスワードが違います。",
        "004" => "対象データが見つかりませんでした。",
        "005" => "ユーザIDが指定されていません。",
        "006" => "パスワードが指定されていません。",
        "007" => "権限がありません。",
        "008" => "検索文字列が指定されていません。",
        "009" => "ユーザ名が指定されていません。",
        "010" => "権限IDが指定されていません。",
        "011" => "検索区分が不正です。",
        "012" => "JSONが無効です。"
        // 必要に応じて追加可能
    ];

    // 該当エラーコードが存在すればメッセージを返す、なければ共通エラー
    return isset($errors[$errorCode]) ? $errors[$errorCode] : $errors["E016"];
}

// １－１．返却データを作成する
// ２－１．レスポンスとしてJSONを出力する
// ３－１．処理を終了する
function errorResponse($errorCode) {
    http_response_code(400);
    header('Content-Type: application/json');

    $response = [
        "result" => "error",
        "errCode" => $errorCode,
        "errMsg" => getErrorMessage($errorCode)
    ];

    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit; // ３ー１．処理を終了する
}
?>
