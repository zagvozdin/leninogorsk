<?
  // create curl resource
  $curl1 = curl_init();

  curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl1, CURLOPT_HEADER, false);

  curl_setopt($curl1, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl1, CURLOPT_SSL_VERIFYHOST, false);

  curl_setopt($curl1, CURLOPT_URL, "https://pogoda.yandex.ru/leninigorsk/details");

  $output = curl_exec($curl1);

  // close curl resource to free up system resources
  curl_close($curl1);


  // convert string
  $output = iconv('UTF-8','WINDOWS-1251//IGNORE',$output);

$pos_beg = strpos($output,'<div class="current-weather__thermometer current-weather__thermometer_type_now">');
$pos_end = strpos($output,'</div>',$pos_beg) + strlen('</div>');



  $output = substr($output,$pos_beg, $pos_end-$pos_beg);
  $output = strip_tags($output);
  if ( strpos($output, '+')===false && ((int)($output))!=0 ) $output = '-'.$output;

  // convert back
  $output = iconv('WINDOWS-1251','UTF-8//IGNORE',$output);

header('Content-Type: text/html; charset=utf-8');
echo $output;

?>