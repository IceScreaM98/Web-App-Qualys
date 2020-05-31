<html lang="en">
    <?php 
        session_start();
            if (!isset($_SESSION["email"])) 
                $mail = "N/A";
            else $mail = $_SESSION["email"];
    ?>
    <head>
        <title>Tag management</title>
        <link rel="stylesheet" type="text/css" href="myStyle.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script>
            function check_button(){
                var email = document.getElementById("email").value;
                var key = document.getElementById("activationKey").value;
                var button = document.getElementById("sendButton");
                if (!is_valid_key(key) || !is_valid_email(email)) button.disabled = true;
                else button.disabled = false;
            }

            function send_request(){
                var xhttp = new XMLHttpRequest();
						xhttp.onreadystatechange = function() {
							if (this.readyState == 4) {
								document.getElementById("sendButton").innerHTML = "Invia";
								document.getElementById("message").innerHTML = this.responseText;
							}
							else document.getElementById("sendButton").innerHTML= '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>';
						};
				
						xhttp.open("POST", "../scripts/create_tag.php", true);
						xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        var key = document.getElementById("activationKey").value;
						xhttp.send("utente=" + '<?php echo $mail ?>' + "&qid=" + key);	
            }

            function is_valid_email(email){
                const substring = "unipr.it";
                var emailValidator = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return email.match(emailValidator) && email.includes(substring);
            }

            function is_valid_key(key){
                var pattern = new RegExp("([A-Z0-9a-z]{8})-([A-Z0-9a-z]{4})-([A-Z0-9a-z]{4})-([A-Z0-9a-z]{4})-([A-Z0-9a-z]{12})");
                return pattern.test(key);
            }

            function logout(){
                window.location.href = '../logout.php';
            }
        </script>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <button class="btn btn-danger" onclick="logout()">Logout</button>
        </nav>
        <div id="container">
            <form>
                <div class="row">
                    <label for="email">Email: </label>
                    <input type="email" name="email" id="email" placeholder="nome.cognome@unipr.it" class="form-control" value=<?php echo $mail ?> disabled>
                </div>
                <div class="row">
                    <label for="activationKey">Key ID: </label>
                    <input type="text" name="activationKey" id="activationKey" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" class="form-control" onkeyup="check_button()">
                </div>
                <div class="row">
                    <button id="sendButton" type="button" class="btn btn-primary" disabled onclick="send_request()">Invia</button>
                </div>
                <div class="row">
                    <div id="message"></div>
                </div>
            </form>
        </div>
    </body>
</html>


