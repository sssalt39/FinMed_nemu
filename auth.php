<?php
/*
file:login.Auth
Author:A.N
Last updated:2025.11.21
権限判定用関数
*/

/**
 * ログイン時に取得した role を渡して、管理者か判定する
 * @param string $role
 * @return bool
 */
function isAdmin($role) {
    return $role === "admin";
}

/**
 * 管理者専用の処理を行う場合に呼ぶ
 * 管理者でなければエラーを返す
 */
function checkAdmin($role) {
    if ($role !== "admin") {
        getErrorMessage('008'); // 権限がありません
    }
}
?>
