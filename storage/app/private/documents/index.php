<?php 

$file_name = getGET('id');
$file_info = pathinfo(__DIR__."/".$file_name);

//ファイルタイプ（MIMEタイプ）を指定
header('Content-Type: application/' . $file_info);
header("Content-Disposition: attachment; filename=" . $file_name);
header('Content-Length: '.filesize(__DIR__."/".$file_name));
readfile(__DIR__."/".$file_name);
exit();

?>