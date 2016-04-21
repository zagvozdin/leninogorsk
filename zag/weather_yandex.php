<?
  // конечный результат, заполняется только эта переменная
  $result_js = null;

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

  $pos_beg = strpos($output,'<div class="tabs-panes__pane tabs-panes__pane_active_yes" role="tabpanel" aria-labelledby="forecasts-tab-1" aria-expanded="true">');
  $pos_end = strpos($output,'<div class="info"><div class="info__ratatuy">',$pos_beg);

  $output = substr($output,$pos_beg, $pos_end-$pos_beg);

/*
// файл для отладки
$output = str_replace('<table ','<table border="1" ',$output);
$f = fopen(__FILE__.'.html','w+');
if ( !$f ) die('file '.__FILE__.'.html'.' create error');
fputs($f,$output);
fclose($f);
*/

  $res = null;
  $p1 = 0;
  $p2 = 0;
  $ERROR = 0; // счётчик ошибок

  // извлечение значений из тега
  function get_val()
  {
    global $output, $p1, $p2, $str1, $str2, $ERROR;
    $len2 = strlen($str2);
    $p1 = strpos($output, $str1, $p1);
    $p2 = strpos($output, $str2, $p1);
    $tmp= substr($output, $p1, $p2-$p1+$len2);
//echo " IN: $tmp\r\n";
    $tmp = trim(strip_tags($tmp));
//echo "OUT: $tmp\r\n\r\n";
    $p1 = $p2;
    if ($tmp==null) $ERROR++; // если значение не найдено, увеличить счётчик ошибок
    return $tmp;
  }

  // сокращения розы ветров
  function wind_shorter($str)
  {
    $translate_table = array(	'северный'=>'сев','северо'=>'сев',
				'южный'=>'южн','юго'=>'юго',
				'западный'=>'зап',
				'восточный'=>'вост');
    return strtr($str, $translate_table);
  }

  $res = array();
  $res_out = array();
  $daypart = null;
  $x = 0;
  for($i=1; $i<=10; $i++)
  {
    // день
    $str1 = '<strong class="forecast-detailed__day-number">';
    $str2 = '</strong>';
    $res['date'] = get_val();
    $res['date'] = str_replace('сегодня', '', $res['date']);
    $res['date'] = str_replace('завтра', '', $res['date']);

  for($j=1;$j<=4;$j++)
   {
//  время суток
    $str1 = '<div class="weather-table__daypart">';
    $str2 = '</div>';
    $daypart = get_val().'<br/>';

//  температура
    $str1 = '<div class="weather-table__temp">';
    $str2 = '</div>';
    $res['temp'] = get_val().'&deg;C';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['temp']);

//  облачность
	// пропустить иконку облачности
	$str1 = '<div class="weather-table__value">';
	$str2 = '</div>';
	get_val();
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['cloudness'] = get_val();
    if ( false ) // заказчику это не нужно
    {
      array_push($res_out, $res['date']);
      array_push($res_out, $daypart.$res['cloudness']);
    }


//  давление
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['pressure'] = get_val().' мм р.с.';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['pressure']);

//  влажность
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['humidity'] = get_val().' влажн';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['humidity']);


//  ветер направление
    $str1 = 'abbr class=" icon-abbr" title="Ветер:';
    $str2 = '">';
    $pre_val = get_val(); // т.к. значение содержится в атрибуте
    $pre_val = str_replace( $str1, null, $pre_val );
    $pre_val = str_replace( $str2, null, $pre_val );
    $wind_direction = wind_shorter($pre_val).'<br/>';
//    array_push($res_out, $res['date']);
//    array_push($res_out, $daypart.$res['wind_direction']);

//  ветер скорость
    $str1 = '<span class="weather-table__wind">';
    $str2 = '</span>';
    $res['wind'] = get_val().' м/с';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$wind_direction.$res['wind']);
   }

echo "<pre>";
print_r($res_out);
echo "</pre>";



    // сформировать JavaScript массив
    while( /*( $ERROR <= 1 ) && */ ($val1 = array_shift($res_out)) )
    {
      $val2 = array_shift($res_out);
      // выбрать только дневные значения
      if ( strpos($val2,'днём<br/>')===false ) continue;
      $x++;
      // при первом вхождении объявить масссив дат и значений
      if( $x == 1 ) $result_js.= "var arr_date = [];\r\nvar arr_data = [];";
      // отрезать время суток
      $val2 = str_replace('днём<br/>', null, $val2 );
      $result_js.= "\r\narr_date[$x]='$val1';";
      $result_js.= "\r\narr_data[$x] = '$val2';";
    }
    $p1 = $p2;
  }

  echo "<pre>\r\nresult_js =\r\n".$result_js."</pre>";

?>