<?php

// Get image info from submit form
$filename = $_FILES["cover_image_file_chooser"]["name"];
$tempname = $_FILES["cover_image_file_chooser"]["tmp_name"];
$file_ext = pathinfo($filename, PATHINFO_EXTENSION);

$group = $_POST["view_book_group"];
$author = $_POST["author"];
$layout_json = json_decode($_POST["layout_json"], true);

// Connect to db
try {
    // host, 'root', '*****': Sakura server
    $pdo = new PDO('mysql:dbname=second_php_db;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('DB_CONNECT: ' . $e->getMessage());
}

foreach ($layout_json as $layout) {

    $layout_str = json_encode($layout["layout"]);

    $sql = "UPDATE `diary_table` SET `page_layout`=:layout WHERE id=:id;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':layout', $layout_str, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':id', $layout["id"], PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

//    echo "id " . $layout["id"] . "'s layout: " . $layout_str;
//    echo "<br>";

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

// Get a number that will not be same as any files in before
$num_images = 0;
$image_counts_filename = "./covers/image_counts.txt";

// Load current number of files from file
if (file_exists($image_counts_filename)) {
    $json_str = file_get_contents($image_counts_filename);

    $image_counts_json = json_decode($json_str, true);

    $num_images = $image_counts_json["num_images"];
}

// Increase the number of images
$num_images++;



// New file name of image to be stored on server
$new_filename = "./covers/" . sprintf("image_%08d", $num_images) . "." . $file_ext;

// Now let's move the uploaded image into the folder: images
if(move_uploaded_file($tempname, $new_filename)) {

    // Save the new number of images to file
    $new_image_counts_json = json_encode(array("num_images" => $num_images));
    $file = fopen($image_counts_filename, "w");
    fwrite($file, $new_image_counts_json);
    fclose($file);

} else {
    echo "Failed to upload image!!";
    exit("");
}

echo $new_filename;

$sql = "SELECT * FROM `book_table` WHERE book_name=:book_name";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':book_name', $group, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

$values = $stmt->fetchAll(PDO::FETCH_ASSOC);

// No record, then INSERT
if(count($values) == 0){
    $sql = "INSERT INTO `book_table`(`author`, `book_name`, `cover_filename`) VALUES " .
                                   "(:author , :book_name , :cover_filename)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":author", $author, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":book_name", $group, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":cover_filename", $new_filename, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)

    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

// Record exists, then UPDATE
else {
    $sql = "UPDATE `book_table` SET `cover_filename`=:cover_filename,`cover_layout`='' WHERE book_name=:book_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_name", $group, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":cover_filename", $new_filename, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)

    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <form id="back_to_view_form" method="post" action="view.php">
        <input type="text" name="view_book_group" value="<?=$group?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        $("#back_to_view_form").submit();
    });

</script>
</html>