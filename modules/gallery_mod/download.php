<?php
// WORK FROM ROOT DIRECTORY
while(!file_exists("index.php")){
  chdir("..");
}

global $_FN;

if(isset($_POST["filename"])){
  $filename = $_POST["filename"];
  $dir = isset($_POST["dir"]) ? $_POST["dir"] . "/" : "";
}
elseif(isset($_GET["filename"])){
  $filename = $_GET["filename"];
  $dir = isset($_GET["dir"]) ? $_GET["dir"] . "/" : "";
}

$file = $_FN["siteurl"] . $dir . $filename;

if (file_exists($file)) {
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".basename($file));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    header("Content-Length: " . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}

?>