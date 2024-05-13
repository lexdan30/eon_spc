<?php
$zip = new ZipArchive();
$zip_name = time().".zip";
$zip->open($zip_name,  ZipArchive::CREATE);

$dir    = 'file/' . $_GET['loc'] .'/';
$files  = array_diff(scandir($dir), array('..', '.'));

foreach ($files as $file) {
   $path = $dir .$file;
  if(file_exists($path)){
    $zip->addFromString(basename($path),  file_get_contents($path)); 
  }else{
       echo"file does not exist";
  }
}
$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zip_name);
header('Content-Length: ' . filesize($zip_name));
readfile($zip_name);

if(file_exists($zip_name)){
    unlink($zip_name);
}
?>