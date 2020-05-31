<html lang="en">
    <?php 
        session_start(); //Se non presente redicrect alla pagina di login
            if (!isset($_SESSION["email"])) 
                header("Location: ../index.php");
            else $mail = $_SESSION["email"];
    ?>
    <head>
        <title>Key request</title>
        <link rel="stylesheet" type="text/css" href="myStyle.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script>
        
            function send_request(){  //per ora mi limito a restituire asset esistenti
                document.getElementById("sendButton").disabled = true;
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                       document.getElementById("sendButton").innerHTML = "Richiedi chiave";
                       var jsonResponse = JSON.parse(xhttp.responseText);
                       show_assets(jsonResponse);
                    }
                    else
                       document.getElementById("sendButton").innerHTML= '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>';
                };
                xhttp.open("POST", "../scripts/create_activationKey.php", true);
                xhttp.send();
            }
            
            
            function show_assets(response){ //popolo la tabella se non ci sono errori
              if (response.hasOwnProperty("error")){
                document.getElementById("sendButton").disabled = false;
                document.getElementById("error").innerHTML = response.error;
                document.getElementById("error").hidden = false;
                return;
              }
              
              if(response.hasOwnProperty("names")){
                var count = response.count;
                var html = "";
                if (count === 0) {
                    html += "<tr>";
                    html += "<td>" + '#' + "</td>";
                    html += "<td>" + "Nessun dispositivo registrato" + "</td>";
                    html += "</tr>";
                }
                else{
                  var asset_names = response.names;
                  for (var i = 1; i <= count; i++){
                    html += "<tr>";
                    html += "<td>" + i + "</td>";
                    html += "<td>" + asset_names[i-1] + "</td>";
                    html += "</tr>";
                  }
                }
                document.getElementById("assets_body_table").innerHTML = html;
                document.getElementById("assets_table").hidden = false;
              }
              
              if(response.hasOwnProperty("activationKey")){
                var activationKey = response.activationKey.Code;
                document.getElementById("show_key").innerHTML = "La tua activation key: " + activationKey;
                document.getElementById("show_key").hidden = false;
              }
              
              
            }

            function logout(){ //redirect allo script di logout
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
                    <h1>Autenticato come: <?php echo $mail ?></h1>
                </div>
                <div class="row">
                    <button id="sendButton" type="button" class="btn btn-primary" onclick="send_request()">Richiedi chiave</button>
                </div>
                <div class="row">
                    <div id="message"></div>
                </div>
            </form>
            <div class="row">
                <table id="assets_table" class="table table-hover" data-toggle="table" hidden>
                    <thead>
                        <tr>
                          <th data-field="numberCol" data-sortable="true" scope="col">#</th>
                          <th data-field="assetnameCol" data-sortable="true" scope="col">Nome dispositivo</th>
                        </tr>
                      </thead>
                      <tbody id="assets_body_table">
                      </tbody>
                </table>
                <div id="error" hidden></div>
            </div>
            <div class="row">
              <h2 id="show_key" hidden></h2>
            </div>
        </div>
    </body>
</html>


