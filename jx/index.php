<?php
error_reporting(-1);
//redirect folks out of here if they try to get here directly.
  $url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  $last = strrpos($url, "/");
  $next_to_last = strrpos($url, "/", $last - strlen($url) - 1);
  $newurl=substr($url,0,$next_to_last)."/";
  header("Location: $newurl");