<?php
   if(isset($_FILES['file'])){
      include('class.filePortrait.php');
      $ex = new filePortrait($_FILES['file']['tmp_name']);
      $ex->decodeImage();
      exit;
   }
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <title>Descriptografar - Exemplo</title>
      <style type="text/css">
         html,body{
            margin:0;
            padding:0;
         }
         #container{
            width:50vw;
            height:40vh;
            text-align:center;
            background:#F5D0A9;
            position:relative;
            top:20vh;
            left:25vw;
            padding-top:10vh;
         }
         span{
            font-size:20px;
            color:#757575;
         }
      </style>
      <script type="text/javascript">
      </script>
   </head>
   <body>
      <div id="container">
         <form action="" method="post" enctype="multipart/form-data">
            <span>Escolha uma imagem para ser descriptografada:</span><br><br><br>
            <input type="file" name="file"><br><br>
            <input type="submit" value="Enviar">
         </form>
      </div>
   </body>
</html>
