<?php

//PHP:コード記述/修正の流れ
//1. insert.phpの処理をマルっとコピー。
//2. $id = $_POST["id"]を追加
//3. SQL修正
//   "UPDATE テーブル名 SET 変更したいカラムを並べる WHERE 条件"
//   bindValueにも「id」の項目を追加
//4. header関数"Location"を「select.php」に変更


//1. POSTデータ取得
$id = $_GET['id'];

//2. DB接続します
//*** function化する！  *****************
$pdo = db_conn();

//３．データ登録SQL作成
// [UPDATE テーブル名 SET カラム1 = 1に入れたいもの, カラム2 = ２に保存したいもの,,,, WHERE 条件]
$stmt = $pdo->prepare('DELETE FROM cgp2_movie_table 
                        WHERE id = :id');
// 数値の場合 PDO::PARAM_INT
$stmt->bindValue(':id', $id, PDO::PARAM_INT); //PARAM_INTなので注意
$status = $stmt->execute(); //実行

// var_dump($status);
// exit();

// echo '<pre>';
// var_dump($status);
// echo '</pre>'

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('SQLError:' . print_r($error, true));
} else {
    header('Location: selectTest2.php');
    exit;
}

// var_dump($result);

?>