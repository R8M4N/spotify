
<?php $nonce = base64_encode(random_bytes(16));
header("X-Frame-Options: SAMEORIGIN");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta charset="iso-8859-2"><meta name="theme-color" content="#1A1A1A" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logowanie</title>
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
  <meta name="handheldFriendly" content="true" /><meta name="MobileOptimized" content="500">
   <meta http-equiv="Content-Security-Policy" content="script-src 'nonce-<?php echo $nonce;?>'; object-src 'self'; base-uri 'self'; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' https://* data:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;connect-src 'self'; form-action 'self';">
    <script src="https://romasolutions.pl/js/jquery.min.js" nonce="<?php echo $nonce;?>"></script>
  <link rel="icon" type="image/svg+xml" href="https://upload.wikimedia.org/wikipedia/commons/1/19/Spotify_logo_without_text.svg">


</head>
<body>



    <main>

    <div class="login-top-box">
        <div class="login-top"  id="login-left">Logowanie</div>
        <div class="login-top"  id="login-right">Rejestracja</div>
        <div style="clear: both;"></div>
    </div>

    <div class="form-box" id="logowanie_formularz">
        <div id="zaloguj-title-text2" > Zaloguj&nbsp;się</div>
        <form id="login-form">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email"  id="logowanie-id" required>
            </div>
            <div class="input-group">
                <label for="password">Hasło</label>
                <input type="password" id="logowanie-haslo" required>
            </div>
   

            <button type="submit" id="logowanie-butt-temp" class="login-button">
                <span class="button-text">Zaloguj się</span>
                <div class="dots-loader">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <div class="success-animation">
                    <div class="checkmark"></div>
                </div>
            </button>
            <div class="error-message">Niepoprawny adres e-mail lub hasło.</div>
        
            <div style="width: 100%;height: 20px;"></div>
        </form>
    </div>

    <div class="form-box" id="rejestracja_formularz" style='max-width:calc(100% - 10px);'>
        <div id="zarejestruj_text" style="user-select:none;color: white;font-family: 'Poppins', sans-serif;font-size: 25px;text-align: center;margin-bottom: 35px;">Zarejestruj się</div>
        
         

            <form>

            <input type="text" id="nickname" autocomplete="off" required class="form-txt" name="lname" minlength="3" maxlength="30" placeholder="Nickname">

            <br><br>
            <input type="text" id="mail" autocomplete="off" required class="form-txt" name="mail" minlength="3" maxlength="30" placeholder="E-mail">
            
            <br><br>
            <input type="password" id="pass1" autocomplete="off" required class="pass-icon-input" name="pass" placeholder="Hasło">
            <br> <br>
            <input type="password" id="pass2" autocomplete="off" required class="pass-icon-input" name="pass2" placeholder="Powtórz hasło">

            <div style="height: 1px;margin: 20px auto;"></div>

            
        
            <button type="button" id="rejestracja" class="submit">Zarejestruj się</button>
        </form>
        <div style="width: 100%;height: 140px;"></div>
    </div>

</main>



<script nonce="<?php echo $nonce;?>">

    $(document).ready(function() {
        $("#rejestracja_formularz").hide();


        function log_left(){
            $("#login-left").css( "background-color", "#1db954" );
            $("#login-right").css( "background-color", "#222222" );
            $("#logowanie_formularz").show();
            $("#rejestracja_formularz").hide();
            document.title = "Logowanie";
        }

        function log_right(){
            $("#rejestracja_formularz").show();
            $( "#logowanie_formularz" ).hide();
            $("#login-left").css( "background-color", "#222222" );
            $("#login-right").css( "background-color", "#1db954" );
            document.title = "Rejestracja";
        }
        function log_rest(){
            $("#rejestracja_formularz").hide();
            $( "#logowanie_formularz" ).hide();
            $("#login-left").hide();
            $("#login-right").hide();
            $("#restart_formularz").show();
        }
        const allowed_characters = /^[\p{L}0-9!@#$%^&*(),.?":{}|<>]+$/u;

        $('#rejestracja').on('click', function() {

            var  mail = $('#mail').val();
            var  pass1 = $('#pass1').val();
            var  pass2 = $('#pass2').val();
            var  nic = $('#nickname').val();

            $.ajax({
                type: 'POST',
                url: 'ajax/rejestracja.php',
                data: {
                    mail:mail,
                    pass1:pass1,
                    nic:nic
                },
                success: function(response) {
                    const obj = JSON.parse(response);
                    if(obj.g==1){
                        
                        $("#mail").val("");
                        $("#pass1").val("");
                        $("#pass2").val("");
                        $('#nickname').val("");

                        $("#logowanie_formularz").show();
                        $("#rejestracja_formularz").hide();
                        $("#login-left").css( "background-color", "#2194d8" );
                        $("#login-right").css( "background-color", "#222222" );
                        location.reload();

                    }

                    
                }
            });
        });


        $("#nickname").on("input", function() {
            var niii = $("#nickname").val();


            if ((allowed_characters.test(niii) && isValidNick(niii)) || $("#nickname").val() === "") {
                $("#nickname").css("border", "1px solid #222222");
                w1=1
            } else {
                $("#nickname").css("border", "1px solid #FF1B1B");
                w1=0
            }

        });


        function isValidNick(text) {
            return /^[A-Za-z0-9]*$/u.test(text);
        }

    
        $("#pass1, #pass2").on("input", function() {
            var pass1 = $("#pass1").val();
            var pass2 = $("#pass2").val();
        
            if ((pass1==pass2 || ($("#pass1").val().length <= 4 || $("#pass2").val().length <= 4)) && ($("#pass1").val() === "" || allowed_characters.test(pass1))) {
                $("#pass1, #pass2").css("border", "1px solid #222222");
                w4=1
            } else {
                $("#pass1, #pass2").css("border", "1px solid #FF1B1B");
                w4=0
            }
        });


        $("#mail").on("input", function() {
            var mailValue = $(this).val();
            var mailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

            if (mailPattern.test(mailValue)) {
                $(this).css("border", "1px solid #222222");
                w5=1
            } else {
                $(this).css("border", "1px solid #FF1B1B");
                w5=0
            }
        });
        


        $(document).on('click', '#login-left', function() {
            log_left();
        });

        $(document).on('click', '#login-right', function() {
            log_right();
        });



    });



$(document).ready(function() {
  
    function button_reset(txt = ""){
        setTimeout(function() {
            $('.dots-loader').removeClass('showing');

            setTimeout(function() {
                if(txt != ""){
                    $('.error-message').text(txt);
                    $('.error-message').show();
                }else{  
                    $('.error-message').hide();
                }
                
                $('.button-text').removeClass('hiding');
                $('.login-button').prop('disabled', false);
            }, 300);
            
        }, 2500);
    }


    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        $('.error-message').hide();
        
        const pulseElement = $('<div class="pulse"></div>');
        $('.login-button').append(pulseElement);
        
        setTimeout(function() {
            pulseElement.remove();
        }, 1000);
        
        $('.button-text').addClass('hiding');
        
        setTimeout(function() {
            $('.dots-loader').addClass('showing');
        }, 300);
        
        $('.login-button').prop('disabled', true);
        
        if (navigator.onLine) {
            var  id = $('#logowanie-id').val();
            var  has = $('#logowanie-haslo').val();
                $.ajax({
                    type: 'POST',
                    url: 'ajax/logowanie.php',
                    data: {
                        id:id,
                        has:has
                    },
                    success: function(response) {
                        const obj = JSON.parse(response);
                        if(obj.gg==1){
                            button_reset(); 
                            window.location.href = "https://romasolutions.pl/spotify/";
                        }
                        else{
                           button_reset(obj.notify); 
                        }
                    }
                });
        }else{button_reset("Jesteś offline");}


    });

    $('input').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
});





</script>
</body></html>
