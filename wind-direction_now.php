<?
/*
  <div class="current-weather__info-row current-weather__info-row_type_wind">
    <span class="current-weather__info-label">Ветер: </span>
    Штиль
  </div>
*/

/*
  <div class="current-weather__info-row current-weather__info-row_type_wind">
    <span class="current-weather__info-label">Ветер: </span> <span class="wind-speed">2,5 м/с</span>
    <abbr class=" icon-abbr" title="Ветер: северный">С</abbr>
    <i class="icon icon_size_12 icon_wind_n icon_wind" data-width="12"></i>
  </div>
*/

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

  // cut off non-wind info
  $pos_beg = strpos($output,'<div class="current-weather__info-row current-weather__info-row_type_wind">');
  $pos_end = strpos($output,'</div>',$pos_beg) + strlen('</div>');
  $output = substr($output,$pos_beg, $pos_end-$pos_beg);

  // direction is present
  if ( strpos($output, '<abbr class=" icon-abbr"')>0 )
  {
    $pos_beg = strpos($output, '<abbr class=" icon-abbr"');
    $pos_end = strpos($output,'</abbr>',$pos_beg) + strlen('</abbr>');
    $output = substr($output,$pos_beg, $pos_end-$pos_beg);
    $output = strip_tags($output);
  }
  else // no direction
  {
    $output = '';
  }

  $output = trim($output);

  // convert back
  $output = iconv('WINDOWS-1251','UTF-8//IGNORE',$output);

header('Content-Type: text/html; charset=utf-8');
echo $output;

?>