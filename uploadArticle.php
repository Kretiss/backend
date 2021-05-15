<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');
require_once "DbConnect.php";
$_POST = json_decode(file_get_contents("php://input"), true);

$name = $_POST["name"];
$content = $_POST["content"];
$categories = $_POST["categories"];

if(empty($name) || empty($content) || empty($categories)) exit(json_encode("empty"));


$dbConnect = new DbConnect;
$dbDoConnect = $dbConnect->connectToDatabase();
if(!$dbDoConnect) exit(json_encode(false));

//kontrola jestli existujou vybrané kategorie
$data = $dbDoConnect->prepare("SELECT EXISTS(SELECT ID FROM categories WHERE category = :category AND ID = :ID)");
foreach ($categories as $category){
    $data->bindValue(":category", $category["name"], PDO::PARAM_STR);
    $data->bindValue(":ID", $category["id"], PDO::PARAM_INT);
    if(!$data->execute()){
        exit(json_encode(false));
    }
}

//kontrola jestli existuje článek se stejným nadpisem
$data = $dbDoConnect->prepare("SELECT ID FROM articles WHERE name = :name");
$data->bindValue(":name", $name, PDO::PARAM_STR);
if(!$data->execute()){
    exit(json_encode(false));
}

if($data->rowCount() > 0){
    exit(json_encode("exists"));
}

//zápis článku
$data = $dbDoConnect->prepare("INSERT INTO articles (name, content, date) VALUES (:name, :content, NOW())");
$data->bindValue(":name", $name, PDO::PARAM_STR);
$data->bindValue(":content", $content, PDO::PARAM_STR);
if(!$data->execute()){
    exit(json_encode(false));
}

$articleID = $dbDoConnect->lastInsertId();

$data = $dbDoConnect->prepare("INSERT INTO art_cat_connect (article_ID, category_ID) VALUES (" .  $articleID . ", :category_ID)");
foreach ($categories as $category){
    $data->bindValue(":category_ID", $category["id"], PDO::PARAM_INT);
    if(!$data->execute()){
        exit(json_encode(false));
    }
}

exit(json_encode("success"));