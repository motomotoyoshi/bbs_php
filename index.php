<?php

    define('DB_USER', 'dbuser');
    define('DB_PASSWORD', 'jM4#HB9V');
    define('DB_NAME', 'paging_php');

    error_reporting(E_ALL & ~E_NOTICE);

    try {
        $dbh = new PDO('mysql:host=localhost;dbname='.DB_NAME,DB_USER,DB_PASSWORD);
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }

    function h($s) {
        return htmlspecialchars($s, ENT_QUOTES, 'utf-8');
    }

    $sql = "select * from comments";
    $comments = array();

    foreach ($dbh->query($sql) as $row) {
        array_push($comments, $row);
    }

    var_dump($comments);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
</head>
<body>
    <h1>簡易掲示板</h1>
    <h2>投稿一覧 （<?php echo count($posts); ?>件）</h2>
    <ul>
        <?php foreach($comments as $comment): ?>
            <li><?php echo h($comment['comment']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
