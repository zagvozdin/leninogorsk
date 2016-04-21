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
      $res = "<a href='https://pogoda.yandex.ru/leninigorsk/details' target=_blank>Погода на сегодня, ".date("d.m.Y")."</a><br/><br/>";
      $res .= get_temp($src)."<br/>";
      $res .= get_press($src)."<br/>";
      $res .= get_humid($src)."<br/>";
      $res .= get_wspeed($src)."<br/>";
      $res .= get_wdirect($src)."<br/>";
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
      $pos_beg = strpos($str,'<div class="current-weather__thermometer current-weather__thermometer_type_now">');
      $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');

      $str = substr($str,$pos_beg, $pos_end-$pos_beg);
      $str = strip_tags($str);
/*
Symbol Name: 	Thin Space
Html Entity: 	&thinsp;
Hex Code: 	&#x2009;
Decimal Code: 	&#8201;
Unicode Group: 	General Punctuation
*/
      $str = str_replace(" ",' ',$str);
      if ( strpos($str, '+')===false && ((int)($str))!=0 ) $str = '-'.$str;

      return "<temp>$str</temp>";
  }

  function get_press( $str )
  {
     // first position
     $pos_beg = strpos($str,'Давление:');

     // pressure itself
     $pos_beg = strpos($str,'</span>', $pos_beg);
     $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');

     $str = substr($str,$pos_beg, $pos_end-$pos_beg);

     $str = strip_tags($str);
     $str = intval($str);
     $str .= ' мм';
     return "<press>$str</press>";
  }

  function get_humid( $str )
  {
      // first position
      $pos_beg = strpos($str,'Влажность:');

      // pressure itself
      $pos_beg = strpos($str,'</span>', $pos_beg);
      $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');

      $str = substr($str,$pos_beg, $pos_end-$pos_beg);
      $str = strip_tags($str);
      return "<humid>$str</humid>";
  }

  function get_wspeed( $str )
  {
      // cut off non-wind info
      $pos_beg = strpos($str,'<div class="current-weather__info-row current-weather__info-row_type_wind">');
      $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);
    
      // speed is present
      if ( strpos($str, '<span class="wind-speed">')>0 )
      {
        $pos_beg = strpos($str, '<span class="wind-speed">');
        $pos_end = strpos($str,'</span>',$pos_beg) + strlen('</span>');
        $str = substr($str,$pos_beg, $pos_end-$pos_beg);
        $str = strip_tags($str);
      }
      else // no speed
      {
        $str = strip_tags($str);
        $str = str_replace('Ветер: ','',$str);
      }
    
      $str = trim($str);
      return "<wspeed>$str</wspeed>";
  }

  function get_wdirect( $str )
  {
      // cut off non-wind info
      $pos_beg = strpos($str,'<div class="current-weather__info-row current-weather__info-row_type_wind">');
      $pos_end = strpos($str,'</div>',$pos_beg) + strlen('</div>');
      $str = substr($str,$pos_beg, $pos_end-$pos_beg);
    
      // direction is present
      if ( strpos($str, '<abbr class=" icon-abbr"')>0 )
      {
        $pos_beg = strpos($str, '<abbr class=" icon-abbr"');
        $pos_end = strpos($str,'</abbr>',$pos_beg) + strlen('</abbr>');
        $str = substr($str,$pos_beg, $pos_end-$pos_beg);
        $str = strip_tags($str);
      }
      else // no direction
      {
        $str = '';
      }

      $str = trim($str);

      return "<wdirect>$str</wdirect>";
  }

?>