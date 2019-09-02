<?php

    define('DB_USER', 'dbuser');
    define('DB_PASSWORD', 'jM4#HB9V');
    define('DB_NAME', 'paging_php');
    define('COMMENTS_PER_PAGE', 5);

    if (preg_match('/^[1-9][0-9]*$/', $_GET['page'])) {
        $page = (int)$_GET['page'];
    }else {
        $page = 1;
    }

    error_reporting(E_ALL & ~E_NOTICE);

    try {
        $dbh = new PDO('mysql:host=localhost;dbname='.DB_NAME,DB_USER,DB_PASSWORD);
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }

    $dataFile = "bbs.dat";

    session_start();

    function setToken() {
        $token = sha1(uniqid(mt_rand(), true));
        $_SESSION['token'] = $token;
    }

    function checkToken() {
        if (empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
            echo '不正なPOST';
            exit;
        }
    }

    function h($s) {
        return htmlspecialchars($s, ENT_QUOTES, 'utf-8');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
        isset($_POST['message']) &&
        isset($_POST['user'])) {

        checkToken();

        $message = trim($_POST['message']);
        $user     = trim($_POST['user']);

        if ($message !== '') {

            $user = ($user === '') ? '名無し' : $user;

            $message = str_replace("\t", ' ', $message);
            $user = str_replace("\t", ' ', $user);

            $postedAt = date('Y-m-d H:i:s');

            $newData  = $message. "\t" . $user. "\t" . $postedAt. "\n";

            $fp = fopen($dataFile, 'a');
            fwrite($fp, $newData);
            fclose($fp);
        }
    } else {
        setToken();
    }

    $posts = file($dataFile, FILE_IGNORE_NEW_LINES);
    $posts = array_reverse($posts);

    $offset = COMMENTS_PER_PAGE * ($page - 1);

    $sql = "select * from comments limit ".$offset.",".COMMENTS_PER_PAGE;
    $comments = array();

    foreach ($dbh->query($sql) as $row) {
        array_push($comments, $row);
    }

    $total = $dbh->query("select count(*) from comments")->fetchColumn();
    $totalPages = ceil($total / COMMENTS_PER_PAGE);

    $from = $offset + 1;
    $to = ($offset + COMMENTS_PER_PAGE) < $total ? ($offset + COMMENTS_PER_PAGE) : $total;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>簡易掲示板</title>
</head>
<body>
    <h1>簡易掲示板</h1>
    <form action="" method="post">
        message: <input type="text" name="message">
        user: <input type="text" name="user">
        <input type="submit" value="投稿">
        <input type="hidden" name="token" value="<?php echo h($_SESSION['token']);?>">
    </form>
    <h2>投稿一覧 （全<?php echo $total; ?>件中<?php echo $from; ?>件〜<?php echo $to;?>件表示）</h2>
    <ul>
        <?php if (count($comments)): ?>
        <?php foreach($comments as $comment): ?>
            <li>
                <?php echo h($comment['comment']); ?> - 
                <?php echo h($comment['created']); ?>
            </li>
        <?php endforeach; ?>
        <?php else:?>
            <li>まだ投稿はありません。</li>
        <?php endif;?>

    </ul>

    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page-1;?>">前</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($page == $i): ?>
            <strong><a href="?page=<?php echo $i;?>"><?php echo $i; ?></a></strong>
        <?php else: ?>
            <a href="?page=<?php echo $i;?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page+1; ?>">次</a>
    <?php endif; ?>
</body>
</html>
