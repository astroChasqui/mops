<?php

  function queryMysql($query)
  {
    $result = mysql_query($query) or die("MySQL error: ".mysql_error());
    return $result;
  }

  function sanitizeString($var)
  {
    $var = strip_tags($var);
    $var = htmlentities($var);
    $var = stripslashes($var);
    return mysql_real_escape_string($var);
  }

?>
