<?php

// Connect to db
try {
    // host, 'root', '*****': Sakura server
    $pdo = new PDO('mysql:dbname=second_php_db;charset=utf8;host=localhost', 'root', '');
} catch (PDOException $e) {
    exit('DB_CONNECT: ' . $e->getMessage());
}

if (isset($_POST["view_book_group"])) {
    $group = $_POST["view_book_group"];
}
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

    if (count($values) == 0) {
        $group = "";
    } else {
        $group = $values[0]["book_group"];
    }
}

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

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM `book_table` WHERE book_name=:book_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_name", $group, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    $cover_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    $values = [];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
    <link rel="stylesheet" href="./css/view_style.css">
</head>
<body>
<div class="left_bar_back_bottom"></div>
    <div class="left_bar_back_style">
        <button id="buy_this_book" class="edit_btn_style"> Buy</button>    
        <button id="see_bookshelf" class="edit_btn_style"> See Bookshelf </button>
        <button id="edit_layout" class="edit_btn_style"> Edit Layout </button>
        <button id="change_cover" class="edit_btn_style"> Change Cover </button>
        <button id="save_layout" class="edit_btn_style"> Save Layout </button>        
        <button id="reset_layout" class="edit_btn_style" hidden> Reset Layout </button>     
        
    </div>
    <div class="view_item_empty_back_style">
    </div>
    <div class="view_main_back_style">
        <div id="book" class="view_two_pages_back_style">
            <div id="page_0" class="view_right_page_back_style">
                <div id="cover_page" class="first_page_all_style" style="background-image: url('<?php
                                                                        if($cover_values){
                                                                            if(count($cover_values) > 0){
                                                                                echo $cover_values[0]["cover_filename"];
                                                                            }
                                                                        }
                                                                         ?>');">
                    <div class="first_page_title_style">
                        <?php
                            if($group == ""){
                        ?>
                                <input id="input_book_title" type="text" class="input_book_title_style" />
                        <?php
                            } else {
                                echo $group;
                            }
                        ?>
                    </div>
                </div>
            </div>
            <?php

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
                return $default_value;
            }

            function printOneItem($layout_json, $item_name, $id, $html_content) {
                echo "<div id='record_".$item_name."_".$id."' class='view_record_".$item_name."_back_style' style='position: relative; left: ".getItemAttr($layout_json, $item_name, 'left', '100px')."; top: ".getItemAttr($layout_json, $item_name, 'top', '50px').";'>\n";
                echo "  <div id='record_" . $item_name . "_rotateable_" . $id . "' class='view_record_item_rotateable_back_style' degree='" . getItemAttr($layout_json, $item_name, 'degree', 0) . "' scale='" . getItemAttr($layout_json, $item_name, 'scale', 1) . "' style='transform: rotate(" . getItemAttr($layout_json, $item_name, 'degree', 0) . "deg) scale(" . getItemAttr($layout_json, $item_name, 'scale', 1) . "'>\n";
                echo "      <div class='view_item_empty_back_style'>\n";
                echo "          <div id='record_".$item_name."_rotate_".$id."' class='view_item_rotate_btn_style'></div>\n";
                echo "      </div>\n";
                echo "      <div id='record_" . $item_name . "_drag_" . $id . "' class='record_drag_trigger_style'></div>\n";
                echo "      " . $html_content . "\n";
                echo "  </div>\n";
                echo "</div>\n";
            }

            $i = 1;
            foreach ($values as $value) {
                $page_layout = json_decode($value["page_layout"], true);
            ?>
            <div id="page_<?=$i?>" class="<?php
                                            if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                            ?>">
                <div id="record_id_<?=$i?>" hidden><?= $value['id'] ?></div>
                <?php
                printOneItem($page_layout, 'image', $i, '<img id="record_image_content_' . $i . '" src="' . $value["image_filename"] . '" class="view_record_image_style" />');
                printOneItem($page_layout, 'datetime', $i, DateTime::createFromFormat('Y-m-d H:i:s', $value["input_date"])->format('Y/m/d H:i'));
                printOneItem($page_layout, 'author', $i, $value["input_author"]);
                printOneItem($page_layout, 'storybook', $i, $value["storybook_name"]);
                printOneItem($page_layout, 'child_name', $i, $value["child_name"]);
                printOneItem($page_layout, 'progress', $i, $value["progress"]);
                printOneItem($page_layout, 'child_feedback', $i, $value["child_feedback"]);

                if($value["input_comment"]){
                    $comment_content = $value["input_comment"];
                }
                else {
                    $comment_content = "No comment";
                }
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
                ?>
<!--                <div id="record_comment_<?= $i ?>" class="view_record_all_comments_style" style="position: relative; left: <?= getItemAttr($page_layout, 'comment', 'left', '80px') ?>; top: <?= getItemAttr($page_layout, 'comment', 'top', '50px') ?>;">
                    <div id="record_comment_rotateable_<?= $i ?>" class="view_record_item_rotateable_back_style" degree="<?= getItemAttr($page_layout, 'comment', 'degree', 0) ?>" scale="<?= getItemAttr($page_layout, 'comment', 'scale', 1) ?>" style="transform: rotate(<?= getItemAttr($page_layout, 'comment', 'degree', 0) ?>deg) scale(<?= getItemAttr($page_layout, 'comment', 'scale', 1) ?>)">
                        <div class="view_item_empty_back_style">
                            <div id="record_comment_rotate_<?= $i ?>" class="view_item_rotate_btn_style"></div>
                        </div>
                        <div id="record_comment_drag_<?= $i ?>" class="record_drag_trigger_style"></div>
                        <div class="view_record_comment_multiple_lines_style">
                            <div class="view_record_comment_one_line_style">
                                <?php 
                                    // if($value["input_comment"]) echo $value["input_comment"]; else echo "No comment"; 
                                ?>
                            </div>
                        </div>
                        
                    </div>
                    
                </div> -->
                <div class="view_item_empty_back_style">
                    <svg class="main-clef-1" viewBox="0 0 100 100">
                        <use xlink:href="#g-clef" x="20" y="120" class="main-clef-1"></use>
                    </svg>
                </div>
            </div>
            <?php
                $i++;
            }
            ?>
            <div id="page_<?=$i?>" class="<?php
                                          if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                          ?>">
                <form id="upload_form" action="write.php" method="post" enctype="multipart/form-data">
                    
                    <div id="image_preview" class="image_preview_style"></div>
                    <div class="form_one_item_back_style">
                        <input type="file" id="image_file_chooser" name="image_file_chooser" hidden>
                    </div>
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
            <?php
            if(($i % 2) == 1){
            ?>
            <div id="page_<?=($i+1)?>" class="view_right_page_back_style">
            </div>
            <?php
                $i++;
            }
            ?>
            <div id="page_<?=($i+1)?>" class="view_left_page_back_style">
                <div class="first_page_all_style">
                    <div class="first_page_title_style">
                    </div>
                </div>
            </div>
        </div>
        <div class="svg_back_style" hidden>
        </div>
        
        <form id="layout_form" method="post" action="write_layout.php"  enctype="multipart/form-data">
            <input type="file" id="cover_image_file_chooser" name="cover_image_file_chooser" hidden>
            <input type="text" name="view_book_group" value="<?=$group?>" hidden />
            <input type="text" id="book_author" name="author" value="<?php
                                                                    if($group == "") {
                                                                        echo $author;
                                                                    } else {
                                                                        echo $value["input_author"];
                                                                    } ?>" hidden/>
            <input id="layout_json" type="text" name="layout_json" hidden/>
        </form>
    </div>    
</body>

<script src='//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.0/TweenMax.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.2/utils/Draggable.min.js'></script>
<script src='//s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js?r=185'></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="./js/turn.js"></script>

<script>
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

        if ($("#group").val() == "") {
            alert("Must input group!");
            return;
        }

        if ($("#author").val() == "") {
            alert("Must input author!");
            return;
        }

        $("#upload_form").submit();
    });

    $("#author").on("change", function () {
        $("#book_author").val($("#author").val());
    });

    $("#input_book_title").on("change", function () {
        $("#group").val($("#input_book_title").val());
    });

</script>

<script>

    let is_ctrl_pressed = false;
    let is_shift_pressed = false;

    let items = [];
    let s = 4;

    function createDraggableItem(itemId) {
        let draggable_item = Draggable.create($(itemId), {
            throwProps: true,
            snap: {
                x: function (endValue) {
                    return Math.round(endValue / s) * s;
                },
                y: function (endValue) {
                    return Math.round(endValue / s) * s;
                }
            },
            onDrag: onDrag,
            onDragEnd: onDragEnd
        });
        return draggable_item;
    }

    function setDraggable(item_name, id) {
        // For draggable
        $("#record_" + item_name + '_' + id).draggable({handle: "#record_" + item_name + '_drag_' + id });

        // For rotate
        $("#record_" + item_name + "_rotateable_" + id).draggable({
            handle: "#record_" + item_name + '_rotate_' + id,
            opacity: 0.001,
            helper: 'clone',
            drag: function (event) {
                var // get center of div to rotate
                    pw = document.getElementById("record_" + item_name + "_rotateable_" + id);
                pwBox = pw.getBoundingClientRect();
                center_x = (pwBox.left + pwBox.right) / 2;
                center_y = (pwBox.top + pwBox.bottom) / 2;

                // get mouse position
                mouse_x = event.pageX;
                mouse_y = event.pageY;
                radians = Math.atan2(mouse_x - center_x, mouse_y - center_y);
                degree = Math.round((radians * (180 / Math.PI) * -1) + 100);

                origin_size = pwBox.width / 2 + 30;
                delta_x = mouse_x - center_x;
                delta_y = mouse_y - center_y;
                new_size = Math.sqrt(delta_x * delta_x + delta_y * delta_y);
                new_scale = new_size / origin_size;

                $("#record_" + item_name + "_rotateable_" + id).attr('degree', (degree + 170));
                $("#record_" + item_name + "_rotateable_" + id).attr('scale', new_scale);

                var rotateCSS = 'rotate(' + (degree + 170) + 'deg) scale(' + new_scale + ')';
                $("#record_" + item_name + "_rotateable_" + id).css({
                    '-moz-transform': rotateCSS,
                    '-webkit-transform': rotateCSS
                });
            }
        });
    }

    $(function () {  
        <?php
        $i = 1;
        foreach ($values as $value) {
        ?>
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

    $(document).ready(function () {
        $('#book').turn({
            width: 840,
            height: 600,
            autoCenter: true
        });

        setLayoutEdit(false);
    });

    function createOneItemJson(item_name, id) {
        let pos_item = $("#record_" + item_name + '_' + id);
        let rot_item = $("#record_" + item_name + "_rotateable_" + id);

        let item_left = pos_item.css("left");
        if (!item_left) item_left = "0px";

        let item_top = pos_item.css("top");
        if (!item_top) item_top = "0px";

        let item_degree = rot_item.attr("degree");
        if (!item_degree) item_degree = 0;

        let item_scale = rot_item.attr("scale");
        if (!item_scale) item_scale = 1.0;

        let item_json = {
            left: item_left,
            top: item_top,
            degree: item_degree,
            scale: item_scale,
        };

        return item_json;
    }

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
        all_pages_json.push(one_page_json);

        <?php
        $i++;
        }
        ?>

        all_pages_json_str = JSON.stringify(all_pages_json);

        $("#layout_json").val(all_pages_json_str);

        $("#layout_form").submit();
    });

    $("#reset_layout").on("click", function() {
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

    $("#edit_layout").on("click", function () {
        edit_layout_enabled = !edit_layout_enabled;
        if (edit_layout_enabled) {
            $("#edit_layout").text("Stop Edit");
            $("#reset_layout").show();
        }
        else {
            $("#edit_layout").text("Edit Layout");
            $("#reset_layout").hide();
        }
        setLayoutEdit(edit_layout_enabled);
    });

    // The image user is choosing
    let selected_cover_file = new File([""], "");

    $("#cover_image_file_chooser").on("change", function () {
        selected_cover_file = this.files[0];

        let reader = new FileReader();
        reader.onloadend = function () {
            $("#cover_page").css("background-image", "url('" + reader.result + "')");
        };
        reader.readAsDataURL(selected_cover_file);
    })

    $("#change_cover").on("click", function () {
        $("#cover_image_file_chooser").click();
    });
</script>

</html>