<!-- Database Imformation Input-->
<?php

include 'db_info_localhost.php';
//include 'db_info_sakura.php';

// Connect to db, Set db name
// exit - show wrong message
try {
    // host, 'root', '*****': Sakura server
//    $pdo = new PDO('mysql:dbname=second_php_db;charset=utf8;host=localhost', 'root', '');
    $pdo = new PDO('mysql:dbname='.getDbName().';charset=utf8;host='.getDbHost(), getDbId(), getDbPw());
} catch (PDOException $e) {
    exit('DB_CONNECT: ' . $e->getMessage());
} 

// Set the name of book, edit the book, and store the content to write.php
// New User或是已註冊卻沒有使用的User登錄後, 因沒有製作任何一本書，以下語法是用來調查該User ID有沒有任何資料存在diary_table這個sheet裡
if (isset($_POST["view_book_group"])) {
    $group = $_POST["view_book_group"];
}
// Diary_table裡面找使用者資料
else {
    $author = $_POST["author"];

    $sql = "SELECT * FROM `diary_table` WHERE input_author=:input_author;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':input_author', $author, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // 如果diary_table沒有任何資料，也就是0的情況下，會顯示空白
    if (count($values) == 0) {
        $group = "";
    } else {
    // 如果diary_table有資料, 也就是不等於0的情況下，顯示的書名就會選擇第一本書的書名
        $group = $values[0]["book_group"];
    }
}
// 只要使用者之前有登錄過書名的話，語法如下：
// book_group =: xxxx (自行喜好填寫)
if ($group != "") {
    $sql = "SELECT * FROM `diary_table` WHERE book_group=:target_book_group;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':target_book_group', $group, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    //之前登錄過的資料(表單的)都會從資料庫裡面讀到$Values的變數裡面 
    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 取得這本書的封面資料
    $sql = "SELECT * FROM `book_table` WHERE book_name=:book_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_name", $group, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
    // 封面資料取得成功的話，從資料庫裡面讀到$cover_values的變數裡
    $cover_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    // Group名稱是空白的話，Values=Page的資料會顯示空白
    $values = [];
}

// 從diary_table裡面的Page_layout資料抓出來
function getItemAttr($layout_json, $item_name, $attr_name, $default_value){
    if ($layout_json) {
        $item_layout = $layout_json[$item_name];
        if ($item_layout) {
            $item_attr = $item_layout[$attr_name];
            if ($item_attr) {
                return $item_attr;
            }
        }
    }
    // 第一次使用的話，資料庫沒有任何資料，就會用defult_value回傳
    return $default_value;
}

// 用PHP的函數生成Book裡面的每一頁的各個元件(照片跟文字)的Html的語法
function printOneItem($layout_json, $item_name, $id, $html_content) {
    echo "<div id='record_".$item_name."_".$id."' class='view_record_".$item_name."_back_style' style='position: relative; left: ".getItemAttr($layout_json, $item_name, 'left', '100px')."; top: ".getItemAttr($layout_json, $item_name, 'top', '50px').";'>\n";
    echo "  <div id='record_" . $item_name . "_rotateable_" . $id . "' class='view_record_item_rotateable_back_style' degree='" . getItemAttr($layout_json, $item_name, 'degree', 0) . "' scale='" . getItemAttr($layout_json, $item_name, 'scale', 1) . "' style='transform: rotate(" . getItemAttr($layout_json, $item_name, 'degree', 0) . "deg) scale(" . getItemAttr($layout_json, $item_name, 'scale', 1) . ");'>\n";
    echo "      <div class='view_item_empty_back_style'>\n";
    echo "          <div id='record_".$item_name."_rotate_".$id."' class='view_item_rotate_btn_style'></div>\n";
    echo "      </div>\n";
    echo "      <div id='record_" . $item_name . "_drag_" . $id . "' class='record_drag_trigger_style'></div>\n";
    echo "      " . $html_content . "\n";
    echo "  </div>\n";
    echo "</div>\n";
}

// 用PHP的函數生成Book封面和封底的Html的語法
function printOneItemWithoutId($layout_json, $item_name, $html_content) {
    echo "<div id='record_".$item_name."' class='view_record_".$item_name."_back_style' style='position: relative; left: ".getItemAttr($layout_json, $item_name, 'left', '100px')."; top: ".getItemAttr($layout_json, $item_name, 'top', '50px').";'>\n";
    echo "  <div id='record_" . $item_name . "_rotateable" . "' class='view_record_item_rotateable_back_style' degree='" . getItemAttr($layout_json, $item_name, 'degree', 0) . "' scale='" . getItemAttr($layout_json, $item_name, 'scale', 1) . "' style='transform: rotate(" . getItemAttr($layout_json, $item_name, 'degree', 0) . "deg) scale(" . getItemAttr($layout_json, $item_name, 'scale', 1) . ");'>\n";
    echo "      <div class='view_item_empty_back_style'>\n";
    echo "          <div id='record_".$item_name."_rotate' class='view_item_rotate_btn_style'></div>\n";
    echo "      </div>\n";
    echo "      <div id='record_" . $item_name . "_drag' class='record_drag_trigger_style'></div>\n";
    echo "      " . $html_content . "\n";
    echo "  </div>\n";
    echo "</div>\n";
}

// HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
    <link rel="stylesheet" href="./css/view_style.css">
</head>
<body>
<!-- 左邊的選單 -->
<div class="left_bar_back_bottom"></div>
<div class="left_bar_back_top"></div>
    <div class="left_bar_back_style">
        <button id="buy_this_book" class="edit_btn_style"> About Us</button>    
        <button id="see_bookshelf" class="edit_btn_style"> Service </button>
        <button id="see_bookshelf" class="edit_btn_style"> News </button>
        <button id="see_bookshelf" class="edit_btn_style"> Contact </button>
        <button id="see_bookshelf" class="edit_btn_style"> Log Out </button>
        
    </div>
    <div class="view_item_empty_back_style">
    </div>
    <!-- 右半邊的書本編輯 -->
    <div class="view_main_back_style">
        <div id="book" class="view_two_pages_back_style">
            <div id="page_0" class="view_right_page_back_style">
                <!-- 用if語法確認封面照片有沒有被設定過 -->
                <div id="cover_page" class="first_page_all_style" style="background-image: url('<?php
                                                                        if($cover_values){
                                                                            if(count($cover_values) > 0){
                                                                                echo $cover_values[0]["cover_filename"];
                                                                            }
                                                                        }
                                                                         ?>');">
                    <!-- 用If語法確認書名是否空白，如果空白就放一個文字方塊 -->
                    <?php
                    $cover_title_html_content = "";
                    if($group == ""){
                        $cover_title_html_content = "<input id='input_book_title' type='text' class='input_book_title_style' />\n";
                    }
                    else {
                    // 如果書名存在就直接秀出書名
                        $cover_title_html_content = 
                            "<div class='first_page_title_style'>\n" .
                                $group . "\n" .
                            "</div>\n";
                    }
                    // 書名的放大、縮小、位置移動(編輯中)
                    if(isset($cover_values["cover_layout"])){
                        $cover_title_layout = $cover_values["cover_layout"];
                    }
                    else {
                        $cover_title_layout = array(
                            'cover_title' => array(
                                'left' => '0px',
                                'top' => '300px',
                                'degree' => 0,
                                'scale' => 1,
                            ),
                        );
                    }
                    // 用php生成書名的Html的語法
                    printOneItemWithoutId($cover_title_layout, "cover_title", $cover_title_html_content);
                    ?>
                </div>
            </div>

            <!-- 用for迴圈跑每一頁的資料設定 -->
            <?php
            // i = 頁數
            $i = 1;
            foreach ($values as $value) {
                // $page_layout讀目前頁書的layout
                $page_layout = json_decode($value["page_layout"], true);
            ?>
            <!-- 每一頁的資料庫都有設定ID, 可以根據ID更新 -->
            <!-- 不想給PHP處理的部分 -->
            <div id="page_<?=$i?>" class="<?php
            // 單數頁設在左邊，偶數頁的右邊，利用php的$i去找
                                            if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                            ?>">
                <div id="record_id_<?=$i?>" hidden><?= $value['id'] ?></div>
                <!-- 用PHP去生成每一頁的照片、日期、繪本名字等的html語法 -->
                <?php
                printOneItem($page_layout, 'image', $i, '<img id="record_image_content_' . $i . '" src="' . $value["image_filename"] . '" class="view_record_image_style" />');
                printOneItem($page_layout, 'datetime', $i, DateTime::createFromFormat('Y-m-d H:i:s', $value["input_date"])->format('Y/m/d H:i'));
                printOneItem($page_layout, 'author', $i, $value["input_author"]);
                printOneItem($page_layout, 'storybook', $i, $value["storybook_name"]);
                printOneItem($page_layout, 'child_name', $i, $value["child_name"]);
                printOneItem($page_layout, 'progress', $i, $value["progress"]);
                printOneItem($page_layout, 'child_feedback', $i, $value["child_feedback"]);

                // 用if語法去設定Comment的生成，如果沒有填寫就不會生成

                if($value["input_comment"]){
                    $comment_content = $value["input_comment"];
                    $comment_content_array = str_split($comment_content, 30);


                    $comment_str = "<div class='view_record_comment_multiple_lines_style'>\n";
                    foreach($comment_content_array as $curr_comment_content) {
                        $comment_str = $comment_str . "   <div class='view_record_comment_one_line_style'>\n";
                        $comment_str = $comment_str . $curr_comment_content . "\n";
                        $comment_str = $comment_str . "   </div>";
                    }
                    $comment_str = $comment_str .
                        "</div>\n";

                    printOneItem($page_layout, 'comment', $i, $comment_str);
                }
                ?>
            </div>

            
            <?php
            // 每處理完1頁，頁數+1
                $i++;
            }
            // 因為中間有穿插不想被PHP處理的HTML語法，所以用好幾個PHP引號
            ?>

            <!-- 最後一頁的輸入表單 -->
            <!-- 表單有可能在頁數的左右兩邊，所以要再寫一次if語法 -->
            <div id="page_<?=$i?>" class="<?php
                                          if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                          ?>">
                <!--表單的語法-->
                <form id="upload_form" action="write.php" method="post" enctype="multipart/form-data">
                    
                    <div id="image_preview" class="image_preview_style"></div>
                    <div class="form_one_item_back_style">
                        <input type="file" id="image_file_chooser" name="image_file_chooser" hidden>
                    </div>
                    <!-- 隱藏 -->
                    <div class="empty">
                        <div class="form_label_style">
                            Book: 
                        </div>
                        <input id="group" type="text" name="group" class="form_text_style" value="<?=$group?>" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Author: 
                        </div>
                        <input type="text" name="author" class="form_text_style" value="<?php
                                                                                        if($group == "") {
                                                                                            echo $author;
                                                                                        } else {
                                                                                            echo $value["input_author"];
                                                                                        } ?>" />
                    </div>
                    <!-- 顯示 -->
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Storybook:
                        </div>
                        <input type="text" name="storybook" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Child's name:
                        </div>
                        <input type="text" name="child_name" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Progress:
                        </div>
                        <input type="text" name="progress" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Child feedback:
                        </div>
                        <input type="text" name="child_feedback" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Comment: 
                        </div>
                        <textarea id="input_comments" name="comments" rows="5" cols="40" class="input_comments_style"></textarea>
                    </div>
                </form>
                <div class="form_button_back_style">
                    <button id="select_image" class="select_image_btn_style"> Select Image </button>
                    <button id="send_btn" class="send_btn_style" onclick="sendForm();"> Send </button>
                </div>
            </div>

            <!-- 為了不讓表單放到最後一頁, 插入空白頁，讓它變成一本書可以合起來 -->
            <?php
            if(($i % 2) == 1){
            ?>
            <div id="page_<?=($i+1)?>" class="view_right_page_back_style">
            </div>
            <?php
                $i++;
            }
            ?>

            <!-- 封底 -->
            <div id="page_<?=($i+1)?>" class="view_left_page_back_style">
                <div class="first_page_all_style">
                    <div class="first_page_title_style">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 編輯書籍的按鈕 -->
        <div class="book_buttons_back_style">
        <button id="edit_layout" class="book_btn_style"> Edit Design </button>
        <button id="change_cover" class="book_btn_style"> Change Cover </button>
        <button id="save_layout" class="book_btn_style"> Save Design </button>        
        <button id="reset_layout" class="book_btn_style"> Reset Design </button>     
        </div>
        
        <!-- 上傳每一頁和封面封底影像的表單到Write_layout_PHP -->
        <form id="layout_form" method="post" action="write_layout.php"  enctype="multipart/form-data">
            <input type="checkbox" id="cover_image_is_chosen" name="cover_image_is_chosen" hidden>
            <input type="file" id="cover_image_file_chooser" name="cover_image_file_chooser" hidden>
            <input type="text" name="view_book_group" value="<?=$group?>" hidden />
            <input type="text" id="book_author" name="author" value="<?php
                                                                    if($group == "") {
                                                                        echo $author;
                                                                    } else {
                                                                        echo $value["input_author"];
                                                                    } ?>" hidden/>
            <input id="layout_json" type="text" name="layout_json" hidden/>
            <input id="cover_layout_json" type="text" name="cover_layout_json" hidden/>
        </form>
    </div>    
</body>

<!-- JS Library -->
<script src='//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.0/TweenMax.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.2/utils/Draggable.min.js'></script>
<script src='//s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js?r=185'></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> 
<script src="./js/turn.js"></script>

<script>
    // Chat App的照片上傳Code再利用
    let selected_image = new Image();
    let selected_image_file = new File([""], "");

    $("#image_file_chooser").on("change", function () {

        // The image is stored in memory
        selected_image_file = this.files[0];

        let reader = new FileReader();
        reader.onloadend = function () {

            selected_image.src = reader.result;
            selected_image.onload = function () {
                $("#image_preview").css("background-image", "url('" + reader.result + "')");
            }
        };
        reader.readAsDataURL(selected_image_file);

    });

    $("#select_image").on("click", function(){
        $("#image_file_chooser").click();
    });

    $("#send_btn").on("click", function () {
        // Submit之前會確認Title是否空白
        if ($("#group").val() == "") {
            alert("Must input group!");
            return;
        }
        // Submit之前會確認author是否空白
        if ($("#author").val() == "") {
            alert("Must input author!");
            return;
        }
        // 沒問題的話就允許submit
        $("#upload_form").submit();
    });

    // author的名稱有更改的話，表單上面的內容也會跟著變更
    $("#author").on("change", function () {
        $("#book_author").val($("#author").val());
    });

    // 第一次使用時，封面輸入任何資訊都會跟表單連動
    $("#input_book_title").on("change", function () {
        $("#group").val($("#input_book_title").val());
    });

</script>

<script>
    // 設定文字跟影像可以縮放和拖拉的函式
    function setDraggableItem(
        draggable_item_name, 
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name) 
    {
        // For draggable 拖拉
        // handle: 指定文字跟影像可以拖拉
        $("#"+draggable_item_name).draggable({handle: "#"+draggable_trigger_name });

        // For rotate 旋轉和縮放
        // rotate_trigger 要點選才能會有縮放功能
        $("#"+rotate_item_name).draggable({
            handle: "#"+rotate_trigger_name,
            // 待查詢
            opacity: 0.001,
            helper: 'clone',

            // 利用滑鼠座標去做角度，三角函數
            drag: function (event) {
                var // get center of div to rotate
                    pw = document.getElementById(rotate_item_name);
                pwBox = pw.getBoundingClientRect();
                center_x = (pwBox.left + pwBox.right) / 2;
                center_y = (pwBox.top + pwBox.bottom) / 2;

                // get mouse position 計算縮放角度和比例
                mouse_x = event.pageX;
                mouse_y = event.pageY;
                radians = Math.atan2(mouse_x - center_x, mouse_y - center_y);
                degree = Math.round((radians * (180 / Math.PI) * -1) + 100);

                origin_size = pwBox.width / 2 + 30;
                delta_x = mouse_x - center_x;
                delta_y = mouse_y - center_y;
                new_size = Math.sqrt(delta_x * delta_x + delta_y * delta_y);
                new_scale = new_size / origin_size;

                // 計算完的角度記錄在HTML的文件裡
                $("#"+rotate_item_name).attr('degree', (degree + 170));
                $("#"+rotate_item_name).attr('scale', new_scale);

                // 用CSS控制大小和旋轉方向
                var rotateCSS = 'rotate(' + (degree + 170) + 'deg) scale(' + new_scale + ')';
                $("#"+rotate_item_name).css({
                    '-moz-transform': rotateCSS,
                    '-webkit-transform': rotateCSS
                });
            }
        });
    }

    // 決定每一頁div的ID，讓Line 424的函數處理
    function setDraggable(item_name, page) {

        let draggable_item_name = "record_" + item_name + '_' + page;
        let draggable_trigger_name = "record_" + item_name + '_drag_' + page;
        let rotate_item_name = "record_" + item_name + "_rotateable_" + page;
        let rotate_trigger_name = "record_" + item_name + '_rotate_' + page;

        setDraggableItem(
            draggable_item_name,
            draggable_trigger_name,
            rotate_item_name,
            rotate_trigger_name
        );
    }

       // 決定封面和封底的div的ID，讓Line 424的函數處理
    function setDraggableWithoutId(item_name){
        let draggable_item_name = "record_" + item_name;
        let draggable_trigger_name = "record_" + item_name + '_drag';
        let rotate_item_name = "record_" + item_name + "_rotateable";
        let rotate_trigger_name = "record_" + item_name + '_rotate';

        setDraggableItem(
            draggable_item_name,
            draggable_trigger_name,
            rotate_item_name,
            rotate_trigger_name
        );
    }

    // 待確認，正在設定封面的標題可以縮放跟拖曳
    $(function () {  
        setDraggableWithoutId('cover_title');
        <?php
        $i = 1;
        foreach ($values as $value) {
        ?>

        // 將某一頁的image設成拖拉的功能
            setDraggable("image", <?=$i?>);
            setDraggable("datetime", <?= $i ?>);
            setDraggable("author", <?= $i ?>);
            setDraggable("storybook", <?= $i ?>);
            setDraggable("child_name", <?= $i ?>);
            setDraggable("progress", <?= $i ?>);
            setDraggable("child_feedback", <?= $i ?>);
            setDraggable("comment", <?= $i ?>);

        <?php
            $i++;
        }
        ?>
    });

    // 參考同期的翻頁效果
    $(document).ready(function () {
        $('#book').turn({
            width: 840,
            height: 600,
            autoCenter: true
        });

        setLayoutEdit(false);
    });

    // 把每一個元件的X, Y座標的旋轉角度和縮放比例，轉成JSON格式，才能透過PHP存到DB去
    function createOneItemJson(item_name, id) {
        let pos_item = $("#record_" + item_name + '_' + id);
        let rot_item = $("#record_" + item_name + "_rotateable_" + id);

        let item_left = pos_item.css("left");
        if (!item_left) item_left = "0px";

        let item_top = pos_item.css("top");
        if (!item_top) item_top = "0px";

        // 角度
        let item_degree = rot_item.attr("degree");
        if (!item_degree) item_degree = 0;

        // 縮放比例
        let item_scale = rot_item.attr("scale");
        if (!item_scale) item_scale = 1.0;

        // 存成json格式
        let item_json = {
            left: item_left,
            top: item_top,
            degree: item_degree,
            scale: item_scale,
        };

        return item_json;
    }

    // 把每一頁的縮放、角度等變數存成json格式，僅存在Client端
    $("#save_layout").on("click", function() {
        let all_pages_json = [];
        let one_page_json = null;

        <?php
        $i = 1;
        foreach ($values as $value) {
        ?>
        one_page_json = {
            id: parseInt($("#record_id_<?= $i ?>").text()),
            layout: {
                image: createOneItemJson("image", <?= $i ?>),
                datetime: createOneItemJson("datetime", <?= $i ?>),
                author: createOneItemJson("author", <?= $i ?>),
                storybook: createOneItemJson("storybook", <?= $i ?>),
                child_name: createOneItemJson("child_name", <?= $i ?>),
                progress: createOneItemJson("progress", <?= $i ?>),
                child_feedback: createOneItemJson("child_feedback", <?= $i ?>),
                comment: createOneItemJson("comment", <?= $i ?>),
            },
        };
        // 每一頁處理完的layout的資料，統整成整本書的內容(Client端)
        all_pages_json.push(one_page_json);

        <?php
        $i++;
        }
        ?>

        // 把json轉成文字，透過form將文字資料傳到DB去，因為DB上面只能儲存文字
        all_pages_json_str = JSON.stringify(all_pages_json);

        // 把文字填入表單
        $("#layout_json").val(all_pages_json_str);

        // Line 346 表單Submit出去
        $("#layout_form").submit();
    });

    // 把所有頁面的設定Reset
    $("#reset_layout").on("click", function() {

        if(!confirm("This can not be undone! Are you sure to reset all layout? ")){
            return;
        }

        let all_pages_json = [];
        let one_page_json = null;

        <?php
        $i = 1;
        foreach ($values as $value) {
            ?>
        one_page_json = {
            id: parseInt($("#record_id_<?= $i ?>").text()),
            layout: {},
        };
        all_pages_json.push(one_page_json);

        <?php
        $i++;
        }
        ?>

        all_pages_json_str = JSON.stringify(all_pages_json);

        $("#layout_json").val(all_pages_json_str);

        $("#layout_form").submit();
    });

    // 編輯Layout
    let edit_layout_enabled = false;

    function setEditOneItem(item_name, id, edit_enabled) {
        $("#record_" + item_name + '_' + id).draggable({ disabled: !edit_enabled });
        if (edit_enabled) {
            $("#record_" + item_name + '_rotate_' + id).attr("class", "view_item_rotate_btn_style");
        }
        else {
            $("#record_" + item_name + '_rotate_' + id).attr("class", "empty");
        }
    }

    function setLayoutEdit(edit_enabled) {

        <?php
        $i = 1;
        foreach ($values as $value) {
            $page_layout = json_decode($value["page_layout"], true);
        ?>
        setEditOneItem("image", <?=$i?>, edit_enabled);
        setEditOneItem("datetime", <?=$i?>, edit_enabled);
        setEditOneItem("author", <?=$i?>, edit_enabled);
        setEditOneItem("storybook", <?= $i ?>, edit_enabled);
        setEditOneItem("child_name", <?= $i ?>, edit_enabled);
        setEditOneItem("progress", <?= $i ?>, edit_enabled);
        setEditOneItem("child_feedback", <?= $i ?>, edit_enabled);
        setEditOneItem("comment", <?=$i ?>, edit_enabled);
        <?php
        $i++;
        }
        ?>
    }

    // 編輯Layout的按鈕功能設定
    $("#edit_layout").on("click", function () {
        edit_layout_enabled = !edit_layout_enabled;
        if (edit_layout_enabled) {
            $("#edit_layout").text("Stop Edit");
        }
        else {
            $("#edit_layout").text("Edit Design");
        }
        setLayoutEdit(edit_layout_enabled);
    });

    // Chat風 等同Select Image功能，只適用在封面
    let selected_cover_file = new File([""], "");

    $("#cover_image_file_chooser").on("change", function () {
        selected_cover_file = this.files[0];

        let reader = new FileReader();
        reader.onloadend = function () {
            $("#cover_page").css("background-image", "url('" + reader.result + "')");
            $("#cover_image_is_chosen").check();
        };
        reader.readAsDataURL(selected_cover_file);
    })

    $("#change_cover").on("click", function () {
        $("#cover_image_file_chooser").click();
    });
</script>

</html>