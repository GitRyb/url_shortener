<?php
include 'functions.php';

if(isset($_POST['submit'])){        
    if (!filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Введена не ссылка';
    } else {
        $url = $_POST['url'];
        if ($shortUrl = checkUrlInDb($url)){
            $p = getTheNumberOfLinkUsage($shortUrl);
        } else {
            $shortUrl = makeItShort();
            saveToDb($url, $shortUrl);
        }
    }      
}
if(isset($_POST['back'])){
    unset($url);
}

if (!redirect()){
    include 'noLink.php';
    return;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Сокращатель ссылок</title>
    <meta name="Description" content="Сделает длинную, некрасивую ссылку короткой и лаконичной">
    <meta name="Keywords" content="urls, shortener, короткие ссылки, укорачиватель ссылок, сократить ссылку, короткий урл, сокращатель ссылок">   
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="icon.ico">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="main-wp">
        <div class="main">
            <h1>Сокращатель ссылок</h1>
            <div class="form-wp">
                <?php if (isset($errors)){
                    foreach ($errors as $error) {
                        echo '<p>' . $error . '</p>';
                    }
                } ?>
                <form action="<?php __FILE__ ?>" method="POST">
                    <input type="text" name="url"
                            <?php if (isset($url)): ?> 
                                readonly="readonly" value="<?= $shortUrl ?>"
                            <?php endif; ?>
                            placeholder="Введите URL">
                    <?php if (isset($url)): ?>
                        <button id="copy_btn">Скопировать</button>
                    <?php endif; ?>
                    <?php if (isset($url)): ?>
                        <input type="submit" name="back" value="В начало">
                    <?php else:?>
                        <input type="submit" name="submit" value="Сократить">
                    <?php endif;?>
                </form>
                <?php if (isset($p)): ?>
                    <div class="info">Ссылка уже существует<br>Ей воспользовались <?= $p . ' ' . howManyTimes($p); ?> </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let source = document.querySelector("input[type='text']");
            let btn = document.querySelector('#copy_btn');
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    navigator.clipboard.writeText(source.value);

                    source.replaceWith(source);
                    document.querySelector("input[type='text']").classList.add('copied');
                });
            };
        });
    </script>
</body>

