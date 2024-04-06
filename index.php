<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storybook Memory Maker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main_back">
        <div class="first_back">
            <div class="first_back_title">Storybook Memory <br> Book Maker </br></div>
            <div id="first_back" class="first_back_btn">
                <button onclick="clickNewUser();" class="btn">
                    <span>New User</span>
                    <svg width="35px" height="32px" viewBox="0 0 13 10">
                    <path d="M1,5 L11,5"></path>
                    <polyline points="8 1 12 5 8 9"></polyline>
                    </svg>
                </button>

                <br>
                <br>
        
                <button onclick="clickLogIn();"  class="btn">
                <span>Log In</span>
                <svg width="35px" height="32px" viewBox="0 0 13 10">
                <path d="M1,5 L11,5"></path>
                <polyline points="8 1 12 5 8 9"></polyline>
                </svg>
                </button>
            </div>

            <div id="form-back" class="form-back-style">
                <div class="form-frame-style">
                
                    <!--  General -->
                    <div class="form-group">
                        <form id="user_submit_form" method="post" action="view.php">
                        <h2 id="form-title" class="heading">New User</h2>
                        <div class="controls">
                        <input type="text" id="ID" class="floatLabel" name="author">
                        <label for="ID">ID</label>
                        </div>
                        <div class="controls">
                        <input type="password" id="password" class="floatLabel" name="password">
                        <label for="password">Password</label>
                        </div>
                        <div id="email-block" class="controls">
                        <input type="text" id="email" class="floatLabel" name="email">
                        <label for="email">Email</label>
                        </div> 
                        </form>
                        <div class="form_btn_back_style">
                            <button onclick="clickSubmit();"  class="btn2">
                            <span id="submit_btn">Create</span>
                            <!-- <svg width="13px" height="10px" viewBox="0 0 13 10"> -->
                            <!-- <path d="M1,5 L11,5"></path> -->
                            <!-- <polyline points="8 1 12 5 8 9"></polyline> -->
                            <!-- </svg> -->
                            </button>
                            <div style="width: 30px;"></div>
                            <button onclick="clickBack();" class="btn2">
                            <span> Back </span>
                            <!-- <svg width="13px" height="10px" viewBox="0 0 13 10"> -->
                            <!-- <path d="M1,5 L11,5"></path>
                            <polyline points="8 1 12 5 8 9"></polyline>
                            </svg> -->
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="second_back">copyrights@Storybook Memory Book Maker All RIghts Reserved.</div>
    </div>


    <!-- <form method="post" action="view.php"> -->
                <!-- <input type="text" name="author" /> -->
                <!-- <input type="submit" /> -->
            <!-- </form> -->
        <!-- </div> -->
    
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    (function($){
  function floatLabel(inputType){
    $(inputType).each(function(){
      var $this = $(this);
      // on focus add cladd active to label
      $this.focus(function(){
        $this.next().addClass("active");
      });
      //on blur check field and remove class if needed
      $this.blur(function(){
        if($this.val() === '' || $this.val() === 'blank'){
          $this.next().removeClass();
        }
      });
    });
  }
  // just add a class of "floatLabel to the input field!"
  floatLabel(".floatLabel");
})(jQuery);

function clickNewUser() {  
    $("#first_back").css({
        opacity: 0.0,
        "z-index": 4,
    });
    $("#form-back").css({
        opacity: 1.0,
        "z-index": 5,
    })
    $("#form-title").text("New User");
    $("#submit_btn").text("Create");
    $("#email-block").show();
}

function clickLogIn() {
    $("#first_back").css({
        opacity: 0.0,
        "z-index": 4,
    });
    $("#form-back").css({
        opacity: 1.0,
        "z-index": 5,
    })
    $("#form-title").text("Log In");
    $("#submit_btn").text("Log in");
    $("#email-block").hide();
}

function clickBack() {
    $("#first_back").css({
        opacity: 1.0,
        "z-index": 5,
    });
    $("#form-back").css({
        opacity: 0.0,
        "z-index": 4,
    })
}

function clickSubmit() {
    if($("#ID").val()=="" || $("#password").val() == ""){
        alert("Must input id and password!");
        return;
    }
    if($("#submit_btn").text() == "Create" && $("#email").val() ==""){
        alert("Must input email!");
        return;
    } 
    $("#user_submit_form").submit();
}

</script>

</html>