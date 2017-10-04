<html>
<head>
  <meta charset="UTF-8">
	<title>財經資訊搜尋網</title>
  <link rel="stylesheet" type="text/css" href="home.css">
</head>
  <div>
    <center>
    <image src="image/title2.png"><br>
    <form name="toInner" method="post" action="content.php">
    <input type="text" id="user_input" name="input" autofocus><br><br><br>
    </form>
    <button onclick="getValue()"><span>FinFetch搜尋 </span></button>
    </center>
  </div>

  <script>
    function getValue(){
      var v = document.getElementById("user_input").value;
      if (v != ""){
        toInner.submit();
      }
    }
  </script>
</html>
