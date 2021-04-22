<?php
function makeItShort (){
    $firstHalf = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $secondHalf = mb_substr(str_shuffle($characters),0 ,7);  
    $shortUrl = $firstHalf . $secondHalf;
    return $shortUrl;
}

function connectToDb () {  //you need to have 4 columns in the table `urls`: `id`, `url`, `short_url`, `count`
    $dbh = new \PDO(
    'mysql:host=localhost;dbname=dbname;',
    'root',
    ''
    );
    $dbh->exec('SET NAMES UTF8');
    return $dbh;
}

function saveToDb (string $url, string $shortUrl) {
    $dbh = connectToDb();
    $stm = $dbh->prepare('INSERT INTO `urls` (`url`, `short_url`) VALUES (:url, :shortUrl)');
    $result = $stm->execute([':url'=>$url, ':shortUrl'=>$shortUrl]);
    return $result;
}

function getLongUrl (string $shortUrl) {
    $dbh = connectToDb();
    $stm = $dbh->prepare('SELECT `url` FROM `urls` WHERE `short_url`=:shortUrl;');
    $stm->execute([':shortUrl' => $shortUrl]);
    $actualLink = $stm->fetch();
    if ($actualLink == null){
        return null;
    }
    return $actualLink['url'];
}

function checkUrlInDb (string $url){ 
    $dbh = connectToDb();
    $stm = $dbh->prepare('SELECT `short_url` FROM `urls` WHERE `url`=:url;');
    $stm->execute([':url'=>$url]);
    $result = $stm->fetch();
    if ($result == null) {
        return false;
    } else {
        return $result['short_url'];
    }
}

function countLinkUsage(string $shortUrl){ //counter of the number of clicks on the short link
    $dbh = connectToDb();
    $stm = $dbh->prepare('UPDATE `urls` SET `count` = `count` + 1 WHERE `short_url`=:shortUrl;');
    $stm->execute([':shortUrl' => $shortUrl]);
}

function getTheNumberOfLinkUsage(string $shortUrl){
    $dbh = connectToDb();
    $stm = $dbh->prepare('SELECT `count` FROM `urls` WHERE `short_url` = :shortUrl');
    $stm->execute([':shortUrl' => $shortUrl]);
    $result = $stm->fetch();
    return $result['count'];
}

function howManyTimes(int $count){
    if ((($count > 10) & ($count < 15)) || (($count % 100 > 10) & ($count % 100 < 15))){
        $end = 'раз';
    } elseif (($count % 10 > 1)&($count % 10 < 5)) {
        $end = 'раза';
    } else {
        $end = 'раз';
    }
    return $end;
}

function redirect(){    
    $shortUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; //link in the address bar
    $pattern = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/"; //short url pattern
    if ($shortUrl == $pattern) {
        return true;
    }
    if (preg_match("~$pattern\S+~", $shortUrl, $match)) {
        $actualLink = getLongUrl($match[0]); //getting the original link from a match
        if ($actualLink == null) {
            return false;
        } else {
            countLinkUsage($shortUrl);
            header('Location: '. $actualLink);
        }
    }
}