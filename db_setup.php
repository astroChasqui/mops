<?php
  include "database.php";
  include "functions.php";
  $query = "CREATE TABLE IF NOT EXISTS main
            (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            path VARCHAR(100), filename VARCHAR(50),
            UNIQUE INDEX (path, filename))";
  queryMysql($query);
  $nlinks_in_db = mysql_num_rows(queryMysql("SELECT * FROM main"));
  $last_link = mysql_fetch_array(queryMysql("SELECT path FROM main
                                             ORDER BY id DESC LIMIT 1"))[0];
  if ($_POST) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $directory = $_POST["directory"];
    if (substr($directory, -1) != "/") $directory .= "/";

    $url_root = "http://austral.as.utexas.edu/michael/observing/HJST/";
    $url = $url_root.$directory;
    $_SESSION["msg1"] = "Looking for jpg images in:<br />$url<br />";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    preg_match_all("/(a href\=\")([^\?\"]*)(\")/i",
                   curl_exec($ch), $matches);
    curl_close($ch);
    $query = "INSERT IGNORE INTO main (path, filename) VALUES ";
    $nfiles = 0;
    $filelist = "";
    foreach ($matches[2] as $match) {
      if (strpos($match, ".jpg")) {
        $filename = str_replace(".jpg", "", $match);
        $query .= "('$directory$match', '$filename'), ";
        $filelist .= "+ ".$filename."<br />";
        $nfiles += 1;
      }
    }
    if ($nfiles) {
      $query = substr($query, 0, -2);
      mysql_query($query);
      if (mysql_affected_rows()) {
        $nrows = mysql_affected_rows();
        if ($nfiles != $nrows) {
          $_SESSION["msg2"] = "Some of the links are already in the ".
                              "database.<br />";
        }
        $_SESSION["msg2"] .= "$nrows rows added to the database.<br />";
      } else {
        $_SESSION["msg2"] .= "All of the links are already in the ".
                             "database.<br />";
      }
      $_SESSION["nfiles"] = $nfiles;
      $_SESSION["filelist"] = $filelist;
    } else {
      $_SESSION["msg2"] .= "Directory requested may not exist or the ".
                           "credentials are invalid.<br />";
    }

    $_SESSION["username"] = $username;
    $_SESSION["password"] = $password;
    $_SESSION["directory"] = $directory;
    header("Location:".$_SERVER["PHP_SELF"]);
    exit();
  } else {
    $_SESSION = array();
    session_destroy();
  }

?>
