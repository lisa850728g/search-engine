<?php
  $articleId = $_GET["a_id"];
  $cnx = mysqli_connect("139.162.23.125:3306", "root", "DifficulT7130");
  mysqli_query($cnx, "set character set 'utf8'");
  mysqli_query($cnx, "SET NAMES UTF8");
  $db_selected = mysqli_select_db($cnx, "cybersite");

  $sql = "SELECT comparison.article_id,comparison.compare_id,comparison.score FROM comparison INNER JOIN fetch_data ON (fetch_data.id=comparison.article_id OR comparison.compare_id=fetch_data.id) WHERE fetch_data.id='$articleId' ORDER BY score DESC LIMIT 5";

  $result = mysqli_query($cnx, $sql);
  if (!$result) {
      die("Error sql: " . mysqli_error($cnx));
  }

  $articles = array();
  for ($i = 0; $i < mysqli_num_rows($result); $i ++) {
      $rs = mysqli_fetch_row($result);
      if ( $rs[1] == $articleId ) {
          $relation = $rs[0];
      } else {
          $relation = $rs[1];
      }
      $sql2 = "SELECT headlines,url FROM fetch_data WHERE id='$relation'";
      $result2 = mysqli_query($cnx, $sql2);
      if (!$result2) {
          die("Error sql: " . mysqli_error($cnx));
      }
      $article = mysqli_fetch_row($result2);
      array_push($articles,$article);
  }

  echo json_encode($articles);
?>
