<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['onlineMovieUrl'])) {
    $onlineMovieUrl = $_POST['onlineMovieUrl'];
} else {
    $onlineMovieUrl = '';
}

if (isset($_POST['title'])) {
    $title = $_POST['title'];
} else {
    $title = '';
}

var_dump($onlineMovieUrl);
var_dump($title);

$id = $_POST['id'];

try {
    $db_name = 'cgp2_movie_db';
    $db_id = 'root';
    $db_pw = '';
    $db_host = 'localhost';
    $pdo = new PDO('mysql:dbname=' . $db_name . ';charset=utf8;host=' . $db_host, $db_id, $db_pw);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 1. 自動コミットモードを無効に設定
    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
} catch (PDOException $e) {
    exit('DB Connection Error:' . $e->getMessage());
}

// 2. トランザクションの開始
$pdo->beginTransaction();

$stmt = $pdo->prepare('UPDATE cgp2_movie_table SET onlineMovieUrl = :onlineMovieUrl, title = :title WHERE id = :id');
$stmt->bindParam(':onlineMovieUrl', $onlineMovieUrl, PDO::PARAM_STR);
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

var_dump($onlineMovieUrl);
var_dump($title);
var_dump($id);

// 3. エラーログの出力とロールバック処理
if ($status === false) {
    $error = $stmt->errorInfo();
    error_log('SQLError: ' . print_r($error, true));
    $pdo->rollBack();
    exit('SQLError:' . print_r($error, true));
}

// 4. コミット処理
$pdo->commit();

// DBへの変更が成功した場合
header('Location: dbdetailTest2.php?id=' . $id);
exit();

echo '更新が成功しました。';

?>