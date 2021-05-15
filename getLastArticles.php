<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');
require_once "DbConnect.php";


$dbConnect = new DbConnect;
$dbDoConnect = $dbConnect->connectToDatabase();
if(!$dbDoConnect) exit(json_encode(false));

//vytahování článků
$data = $dbDoConnect->prepare("SELECT ID, name, content, DATE_FORMAT(date, '%d.%m.%Y') AS date FROM articles ORDER BY ID DESC LIMIT 3");
if(!$data->execute()){
    exit(json_encode(false));
}

if($data->rowCount() > 0){
    $selectedArticles = $data->fetchAll(PDO::FETCH_ASSOC);
} else{
    exit(json_encode("empty"));
}


//vytahování kategorií ke článkům
$statement = "SELECT categories.category FROM categories
              INNER JOIN art_cat_connect ON categories.ID = art_cat_connect.category_ID
              WHERE art_cat_connect.article_ID = :article_ID";

$data = $dbDoConnect->prepare($statement);
for($i = 0; $i < count($selectedArticles); $i++){
    $categories = [];
    $data->bindValue(":article_ID", $selectedArticles[$i]["ID"], PDO::PARAM_INT);
    if(!$data->execute()){
        exit(json_encode(false));
    }
    $selectedCategories = $data->fetchAll(PDO::FETCH_ASSOC);
    foreach($selectedCategories as $category){
        array_push($categories, $category);
    }
    $selectedArticles[$i]["categories"] = $categories;

}

$allData["articles"] = $selectedArticles;


//počet článků v jednotlivých kategoriích
$data = $dbDoConnect->prepare("SELECT * FROM categories");
if(!$data->execute()){
    exit(json_encode(false));
}
$selectedCategories = $data->fetchAll(PDO::FETCH_ASSOC);

$articlesInCategoriesCount = [];
$data = $dbDoConnect->prepare("SELECT count(article_ID) AS count FROM art_cat_connect WHERE category_ID = :category_ID");
foreach($selectedCategories as $category){
    $data->bindValue(":category_ID", $category["ID"], PDO::PARAM_INT);
    if(!$data->execute()){
        exit(json_encode(false));
    }
    $count = $data->fetch(PDO::FETCH_ASSOC);
    array_push($articlesInCategoriesCount, array("name" => $category["category"], "count" => $count["count"]));
}

$allData["articlesInCategoriesCount"] = $articlesInCategoriesCount;


//počet použitých kategorií
$data = $dbDoConnect->prepare("SELECT category_ID FROM art_cat_connect GROUP BY category_ID");
if(!$data->execute()){
    exit(json_encode(false));
}
$count = $data->fetchAll(PDO::FETCH_ASSOC);
$allData["usedCategoriesCount"] = count($count);

$allData["loaded"] = true;
exit(json_encode($allData));