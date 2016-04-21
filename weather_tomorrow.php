<?
  $timer = microtime( true );

  $param = isset( $_GET['p'] )? $_GET['p'] : null;
  // create curl resource
  $curl1 = curl_init();

  curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl1, CURLOPT_HEADER, false);

  curl_setopt($curl1, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl1, CURLOPT_SSL_VERIFYHOST, false);

  curl_setopt($curl1, CURLOPT_URL, "https://pogoda.yandex.ru/leninigorsk/details");

  $src = curl_exec($curl1);

  // close curl resource to free up system resources
  curl_close($curl1);

  // convert string
//  $src = iconv('UTF-8','WINDOWS-1251//IGNORE',$src);

  // get for tomorrow only
  $pos_beg = strpos($src,'<span class="forecast-detailed__day-name">завтра</span>');
  $pos_beg = strpos($src,'<dd class="forecast-detailed__day-info">',$pos_beg);
  $pos_end = strpos($src,'</dd>',$pos_beg) + strlen('</dd>');
  $src = substr($src,$pos_beg, $pos_end-$pos_beg);

  // exclude morning
  $pos_beg = strpos($src,'<td class="weather-table__body-cell weather-table__body-cell_type_daypart">');
  // get daytime
  $pos_beg = strpos($src,'<td class="weather-table__body-cell weather-table__body-cell_type_daypart">', $pos_beg+1);
  $pos_end = strpos($src,'<td class="weather-table__body-cell weather-table__body-cell_type_daypart">',$pos_beg+1);
  $src = substr($src,$pos_beg, $pos_end-$pos_beg);

  $res = null;
  switch( $param )
  {
    case 'temp':
      $res = get_temp($src);
      break;

    case 'press':
      $res = get_press($src);
      break;

    case 'humid':
      $res = get_humid($src);
      break;

    case 'wspeed':
      $res = get_wspeed($src);
      break;

    case 'wdirect':
      $res = get_wdirect($src);
      break;

    default:
      $res .= 'Погода на завтра'.'<br/>';
      $res .= get_temp($src).'<br/>';
      $res .= get_press($src).'<br/>';
      $res .= get_humid($src).'<br/>';
      $res .= get_wspeed($src).'<br/>';
      $res .= get_wdirect($src).'<br/>';
      $res .= '<br/><font color=grey size=1>время выполнения '.round(microtime(true)-$timer,3).' сек.</font>';
  }

  // convert back
//  $res = iconv('WINDOWS-1251','UTF-8//IGNORE',$res);

header('Content-Type: text/html; charset=utf-8');
echo $res;


////////////////////////////////////////////////////
// declaration section
////////////////////////////////////////////////////
  function get_temp( $str )
  {
      $pos_beg = strpos($str,'<div class="weather-table__temp">');
      $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');
      $str = substr($str, $pos_beg, $pos_end-$pos_beg);

      $str = strip_tags($str).'°C';
      if ( strpos($str, '+')===false && ((int)($str))!=0 ) $str = '-'.$str;
      return $str;
  }

  function get_press( $str )
  {
      // get pressure value
      $pos_beg = strpos($str,'<td class="weather-table__body-cell weather-table__body-cell_type_air-pressure">');
      $pos_end = strpos($str,'</td>',$pos_beg) + strlen('</td>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);

      $str = strip_tags($str).' мм';
      return $str;
  }

  function get_humid( $str )
  {
      // get humidity value
      $pos_beg = strpos($str,'<td class="weather-table__body-cell weather-table__body-cell_type_humidity">');
      $pos_end = strpos($str,'</td>',$pos_beg) + strlen('</td>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);

      $str = strip_tags($str);
      return $str;
  }

  function get_wspeed( $str )
  {
      // get wind speed value
      $pos_beg = strpos($str,'<span class="wind-speed">');
      $pos_end = strpos($str,'</span>',$pos_beg) + strlen('</span>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);

      $str = strip_tags($str).' м/с';
      return $str;
  }

  function get_wdirect( $str )
  {
      // get wind speed value
      $pos_beg = strpos($str,'<td class="weather-table__body-cell weather-table__body-cell_type_wind">');
      $pos_end = strpos($str,'</td>',$pos_beg) + strlen('</td>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);

      // letter only
      $pos_beg = strpos($str,'<abbr">');
      $pos_end = strpos($str,'</abbr>',$pos_beg) + strlen('</abbr>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);

      $str = strip_tags($str);
      return $str;
  }

?>