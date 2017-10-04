<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// header("Content-Type:text/html;charset=utf-8");
require_once "CKIPClient.php";

define("CKIP_SERVER", "140.109.19.104");
define("CKIP_PORT", 1501);
define("CKIP_USERNAME", "u10316011");
define("CKIP_PASSWORD", "lisa");

$client_obj = new CKIPClient(
   CKIP_SERVER,
   CKIP_PORT,
   CKIP_USERNAME,
   CKIP_PASSWORD
);

if (isset($_POST["input"])) {
    $text = $_POST["input"];
    $_SESSION["search_text"] = $text;
    // redirect (GET) content.php?page=1
    header('Location: content.php?page=1');
} else {
    $text = $_SESSION["search_text"];
}
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>財經資訊搜尋網</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" type="text/css" href="content.css">
</head>
  <form name="myForm" method="post">
    <img src="image/title_next.png" action="home.php">
    <input type="text" name="input" value="<?php echo $text;?>"autofocus>
    <button type="submit">Search</button>
  </form>
</html>
<?php

$return_text = $client_obj->send($text);

$return_sentence = $client_obj->getSentence();

$return_term = $client_obj->getTerm();

$split = array();
for ($i = 0; $i < count($return_term); $i++) {
    if ($return_term[$i]["tag"] != 'COMMACATEGORY' && $return_term[$i]["tag"] != 'PERIODCATEGORY' && $return_term[$i]["tag"] != 'EXCLAMATIONCATEGORY'
        ) {
        $split[] = $return_term[$i]["term"];
    }
}

$user_input = implode(" ", $split);
$cnx = mysqli_connect("139.162.23.125:3306", "root", "DifficulT7130");
if (!$cnx) {
    die("Error connect: " . mysqli_error($cnx));
}
mysqli_query($cnx, "set character set 'utf8'");
mysqli_query($cnx, "SET NAMES UTF8");

$db_selected = mysqli_select_db($cnx, "cybersite");
if (!$db_selected) {
    die("Error db: " . mysqli_error($cnx));
}

$sql = "SELECT HEADLINES,CONTENT,URL,AUTHOR,GETFROM,PUBLISHED, MATCH (HEADLINES,CONTENT) AGAINST ('$user_input' IN BOOLEAN MODE) AS score
          FROM fetch_data WHERE MATCH (HEADLINES,CONTENT) AGAINST ('$user_input' IN BOOLEAN MODE) ORDER BY score DESC, PUBLISHED DESC";
$result = mysqli_query($cnx, $sql);
if (!$result) {
    die("Error sql: " . mysqli_error($cnx));
}

//幾筆資料
$data_nums = mysqli_num_rows($result);
//一頁10筆
$per = 10;
$pages = ceil($data_nums/$per);
//假如$_GET["page"]未設置，則設定起始頁數，若已設置，則確認頁數只能夠是數值資料
if (!isset($_GET["page"])) {
    $page = 1;
} else {
    $page = intval($_GET["page"]);
}
//每一頁開始的資料序號
$start = ($page - 1) * $per;
if ($start != 0) {
    $start += 1;
}
$limit_sql =  $sql. " LIMIT " . $start . "," . $per;

$result = mysqli_query($cnx, $limit_sql) or die("Error");

function get_str($str, $max_length)
{
    if (strlen($str) > $max_length) {
        $check_num = 0;
        for ($i=0; $i < $max_length; $i++) {
            if (ord($str[$i]) > 127) {
                $check_num++;
            }
        }
        if ($check_num % 3 == 0) {
            $str = substr($str, 0, $max_length)."...";
        } elseif ($check_num % 3 == 1) {
            $str = substr($str, 0, $max_length + 2)."...";
        } elseif ($check_num % 3 == 2) {
            $str = substr($str, 0, $max_length + 1)."...";
        }
    }
    return $str;
}

if ($data_nums == 0) {
    echo "<font class=\"result\">無搜尋結果</font>";
} else {
    echo "<font class=\"result\">有 $data_nums 項結果</font><br>";
    echo "<div class=\"list-articles\">";
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $headlines = get_str($row['HEADLINES'], 75);
        $url = $row['URL'];
        $show_url = get_str($url, 60);
        $content = $row['CONTENT'];
        $published = $row['PUBLISHED'];
        $split_date = explode("-", $published);
        $show_date = $split_date[0] . "年" . $split_date[1] . "月" . $split_date[2] . "日 - ";
        $get_from = $row['GETFROM'];

        $lastPos = 0;
        $positions = array();
        $highlighted = array();
        $wordCount = array();

        foreach ($split as $word) {
            $highlighted[] = "<font color='#ce4635'>".$word."</font>";
        }
        $highlight = strtr($content, array_combine($split, $highlighted));
        $pos = strpos($highlight, $highlighted[0], 0);
        for ($j = 0 ; $j < count($split) ; $j ++) {
            if (strstr($highlight, $highlighted[$j]) != null) {
                $from_first = strstr($highlight, $highlighted[$j]);
            }
            //find all occurrences of a substring in a string
            while (($lastPos = strpos($highlight, $split[$j], $lastPos))!== false) {
                $positions[] = $lastPos;
                $lastPos = $lastPos + strlen($split[$j]);
            }
            $wordCount[$j] = count($positions);
            if (!empty($positions)) {
                unset($positions);
                $positions = array();
            }
        }
        $show_content = get_str($from_first, 290);

        echo "<div class=\"article\">";
        echo "<a Target='_blank' href=$url> $headlines </a><br>";
        echo "<font class=\"publisher\"> &#9656; $get_from </font>";
        echo "<font class=\"url-font\"> $show_url </font><br>";
        echo "<font class=\"date-font\"> $show_date </font>";
        echo "<font class=\"content-font\"> $show_content </font><br>";
        echo "<font class=\"word-count\"> 搜尋到的字詞：";
        for ($i = 0 ; $i < count($split) ; $i ++) {
            echo $split[$i] . " " . $wordCount[$i] . " 個 ";
        }
        echo "</font></div>";
    }
    echo "</div>";
}

echo "<div class=\"relative-articles\">
      <h2>Relative Articles</h2>
      <table class=\"table\">
        <thead>
          <tr>
            <th>Order</th>
            <th>Title</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>康師傅、統一、旺旺還能再輝煌嗎？那些叱吒... </td>
          </tr>
        </tbody>
      </table>
      </div>";

echo "<div class=\"page\">";
$previous_page = $page-1;
if ($previous_page != 0) {
    echo "<a href=?page=" . $previous_page . " class=\"navi-paging\">← Previous</a>";
} else {
    echo "<div class=\"no-navi\"/>";
}
for ($i = 1 ; $i <= $pages ; $i ++) {
    if ($i == $page) {
        echo "<font>" . $i . " </font>";
    } else {
        if ($page > 6) {
            if ($page-6 < $i && $i < $page+5) {
                echo "<a href=?page=" . $i . " class=\"page-index\">" . $i . "</a> ";
            }
        } else {
            if (10 - $i >= 0) {
                echo "<a href=?page=" . $i . " class=\"page-index\">" . $i . "</a> ";
            }
        }
    }
}
$next_page = $page+1;
if ($next_page != $pages +1 && $pages > 1) {
    echo "<a href=?page=".$next_page." class=\"navi-paging\">Next →</a>";
}
echo "</div>";

mysqli_free_result($result);
mysqli_close($cnx);

?>
