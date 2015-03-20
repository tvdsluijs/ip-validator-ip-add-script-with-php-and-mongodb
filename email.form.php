<?php
/**
 * Created by PhpStorm.
 * User: theovandersluijs
 * Date: 19/03/15
 * Time: 10:38
 *
 * Please donate a coffee, to keep me coding on this url shortner !!!
 * Bitcoin : 18aJm8qj47iafT5gTgHrBAXzboDS8jEfZM
 * Paypal : http://snurl.eu/coffee
 *
 */
global $email_okay_activation;
?>
<!DOCTYPE HTML>
<html>
<head>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <style>
        @import url(http://fonts.googleapis.com/css?family=Fauna+One|Muli);
        #mainform{
            width:960px;
            margin:20px auto;
            padding-top:20px;
            font-family: 'Fauna One', serif;
        }
        #form{
            border-radius:2px;
            padding:20px 30px;
            box-shadow:0 0 15px;
            font-size:14px;
            font-weight:bold;
            width:350px;
            margin:20px 250px 0 35px;
            float:left;

        }
        h3{
            text-align:center;
            font-size:20px;
        }
        input{
            width:100%;
            height:35px;
            margin-top:5px;
            border:1px solid #999;
            border-radius:3px;
            padding:5px;
        }
        input[type=button]{
            background-color:#123456;
            border:1px solid white;
            font-family: 'Fauna One', serif;
            font-Weight:bold;
            font-size:18px;
            color:white;
        }
        textarea{
            width:100%;
            height:80px;
            margin-top:5px;
            border-radius:3px;
            padding:5px;
            resize:none;
        }
        span{
            color:red
        }
        #note{
            color:black;
            font-Weight:400;
        }
        #returnmessage{
            font-size:14px;
            color:green;
            text-align:center;
        }
        .error {color: #FF0000;}
    </style>
</head>
<body>
<p id="returnmessage"></p>
<div id="mailform">
    <form id="form">
        <label>Email: <span>*</span></label>
        <input type="text" id="email" placeholder="Email" autocomplete="off"/>
        <input type="button" id="submit" value="Send Email"/>
    </form>
</div>
<script>
    $(document).ready(function() {
        $("#submit").click(function() {

            var email = $("#email").val();
            $("#returnmessage").empty(); // To empty previous error/success message.
// Checking for blank fields.
            if (email == '') {
                alert("Please Fill Required Fields");
            } else {
                $("#submit").hide();
                $("#returnmessage").append('sending data!');
// Returns successful data submission message when the entered information is stored in database.
                $.post("index.php", {
                    add_id: 1,
                    email: email
                }, function(data) {
                    $("#submit").show();
                    $("#returnmessage").empty();
                    $("#returnmessage").append(data); // Append returned message to message paragraph.
                    if(data == "<?php echo $email_okay_activation;?>"){
                        $("#form")[0].reset(); // To reset form fields on success.
                        $("#form").hide(); // To reset form fields on success.
                    }
                });
            }
        });
    });
</script>
</body>
</html>