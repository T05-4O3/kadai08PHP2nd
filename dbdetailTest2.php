<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$id = isset($_GET['id']) ? $_GET['id'] : '';

try {
    $db_name = 'cgp2_movie_db'; //データベース名
    $db_id   = 'root'; //アカウント名
    $db_pw   = ''; //パスワード：MAMPは'root'
    $db_host = 'localhost'; //DBホスト
    $pdo = new PDO('mysql:dbname=' . $db_name . ';charset=utf8mb4;host=' . $db_host, $db_id, $db_pw);
} catch (PDOException $e) {
    exit('DBConnectError' . $e->getMessage());
}

//２．データ取得SQL作成
$stmt = $pdo->prepare("SELECT * FROM cgp2_movie_table WHERE id = :id;");
$stmt->bindValue(':id', $id, PDO::PARAM_INT); //PARAM_INTなので注意
$status = $stmt->execute(); //実行

$result = '';
if ($status === false) {
    $error = $stmt->errorInfo();
    exit('SQLError:' . print_r($error, true));
} else {
    $result = $stmt->fetch();
}

// 埋め込みコードを取得する関数
function getEmbedCode($url) {
    if ($url && isYouTubeUrl($url)) {
        $videoId = getYouTubeVideoId($url);
        if ($videoId) {
            return getYouTubeEmbedCodeWithAPI($videoId);
        } else {
            return '<p class="error">指定されたYouTube動画のURLが無効です。</p>';
        }
    } else if ($url && isVimeoUrl($url)) {
        $videoId = getVimeoVideoId($url);
        if ($videoId) {
            return getVimeoEmbedCode($videoId);
        } else {
            return '<p class="error">指定されたVimeo動画のURLが無効です。</p>';
        }
    } else {
        return '<p class="error">YouTubeまたはVimeoのURLを入力してください。</p>';
    }
}

// YouTubeのURLかどうかを判定する関数
function isYouTubeUrl($url) {
    return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false || strpos($url, 'youtube-nocookie.com') !== false;
}

// VimeoのURLかどうかを判定する関数
function isVimeoUrl($url) {
    return strpos($url, 'vimeo.com') !== false;
}

// YouTubeのURLから動画IDを取得する関数
function getYouTubeVideoId($url) {
    $regex = '/(?:\?v=|&v=|youtu.be\/|\/embed\/|\/v\/|\/watch\?v=)([^#\&\?]{11})/';
    preg_match($regex, $url, $matches);
    if (isset($matches[1])) {
        return $matches[1];
    } else {
        return null;
    }
}

// YouTubeの埋め込みコードを取得する関数
function getYouTubeEmbedCodeWithAPI($videoId) {
    $apiKey = ''; // APIキーを指定してください
    $apiUrl = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $videoId . '&key=' . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['items']) && count($data['items']) > 0) {
        $embedCode = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allowfullscreen></iframe>';
        $title = $data['items'][0]['snippet']['title'];
        return "<div class='embedCode'>$embedCode</div>";
    } else {
        return '<p class="error">指定されたYouTube動画が見つかりませんでした。</p>';
    }
}

// VimeoのURLから埋め込みコードを取得する関数
function getVimeoEmbedCode($videoId) {
    $apiUrl = 'https://vimeo.com/api/v2/video/' . $videoId . '.json';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data[0])) {
        $embedCode = $data[0]['html'];
        $title = $data[0]['title'];
        return "<div class='embedCode'>$embedCode</div><p class='videoTitle'>$title</p>";
    } else {
        return '<p class="error">指定されたVimeo動画が見つかりませんでした。</p>';
    }
}

$onlineMovieUrl = isset($result['onlineMovieUrl']) ? $result['onlineMovieUrl'] : '';
$embedCode = getEmbedCode($onlineMovieUrl);
$title = isset($result['title']) ? $result['title'] : '';

// 削除処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $deleteId = isset($_POST['id']) ? $_POST['id'] : '';

    $stmt = $pdo->prepare('DELETE FROM cgp2_movie_table WHERE id = :id');
    $stmt->bindValue(':id', $deleteId, PDO::PARAM_INT);
    $status = $stmt->execute();

    if ($status === false) {
        $error = $stmt->errorInfo();
        exit('SQLError:' . print_r($error, true));
    } else {
        header('Location: ./selectTest2.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CGP2 動画データベース</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Head[Start] -->
    <header>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="dbregisterTest2.php">登録画面へ</a>
                </div>
            </div>
        </nav>
    </header>
    <!-- Head[End] -->

    <!-- Main[Start] -->
    <div class="register">
        <form id="form" method="POST" action="./updateTest2.php">
            <div id="urlForm">
                <h2 class="onlineMovieUrl">動画を確認</h2>
                <input type="text" id="onlineMovieUrl" onblur="checkUrlValidity()" name="onlineMovieUrl" value="<?= isset($result['onlineMovieUrl']) ? $result['onlineMovieUrl'] : ''; ?>" placeholder="YouTubeかVimeoのURLを入力">
                <div class="urlView">
                    <div class="title" id="videoContainer">
                        <?php echo isset($embedCode) ? $embedCode : ''; ?>
                    </div>
                    <div class="titleView">
                        <div class="title-label">
                            <h2 class="onlineMovieUrl">タイトル：</h2>
                        </div>
                        <input type="text" id="titleInput" name="title" value="<?= isset($result['title']) ? $result['title'] : ''; ?>"></div>
                </div>
            </div>

            <input type="hidden" name="id" value="<?= $result['id'] ?>">
            <input type="submit" value="更新">
        </form>
        <form id="deleteForm" method="GET" action="./dbdeleteTest2.php">
            <input type="hidden" name="id" value="<?= $result['id'] ?>">
            <input type="submit" value="削除">
        </form>
    </div>
    <!-- Main[End] -->

    <!-- <script src="main2.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- <script src="./detail.js"></script> -->
</body>

</html>