<?
  // �������� ���������, ����������� ������ ��� ����������
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
// ���� ��� �������
$output = str_replace('<table ','<table border="1" ',$output);
$f = fopen(__FILE__.'.html','w+');
if ( !$f ) die('file '.__FILE__.'.html'.' create error');
fputs($f,$output);
fclose($f);
*/

  $res = null;
  $p1 = 0;
  $p2 = 0;
  $ERROR = 0; // ������� ������

  // ���������� �������� �� ����
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
    if ($tmp==null) $ERROR++; // ���� �������� �� �������, ��������� ������� ������
    return $tmp;
  }

  // ���������� ���� ������
  function wind_shorter($str)
  {
    $translate_table = array(	'��������'=>'���','������'=>'���',
				'�����'=>'���','���'=>'���',
				'��������'=>'���',
				'���������'=>'����');
    return strtr($str, $translate_table);
  }

  $res = array();
  $res_out = array();
  $daypart = null;
  $x = 0;
  for($i=1; $i<=10; $i++)
  {
    // ����
    $str1 = '<strong class="forecast-detailed__day-number">';
    $str2 = '</strong>';
    $res['date'] = get_val();
    $res['date'] = str_replace('�������', '', $res['date']);
    $res['date'] = str_replace('������', '', $res['date']);

  for($j=1;$j<=4;$j++)
   {
//  ����� �����
    $str1 = '<div class="weather-table__daypart">';
    $str2 = '</div>';
    $daypart = get_val().'<br/>';

//  �����������
    $str1 = '<div class="weather-table__temp">';
    $str2 = '</div>';
    $res['temp'] = get_val().'&deg;C';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['temp']);

//  ����������
	// ���������� ������ ����������
	$str1 = '<div class="weather-table__value">';
	$str2 = '</div>';
	get_val();
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['cloudness'] = get_val();
    if ( false ) // ��������� ��� �� �����
    {
      array_push($res_out, $res['date']);
      array_push($res_out, $daypart.$res['cloudness']);
    }


//  ��������
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['pressure'] = get_val().' �� �.�.';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['pressure']);

//  ���������
    $str1 = '<div class="weather-table__value">';
    $str2 = '</div>';
    $res['humidity'] = get_val().' �����';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$res['humidity']);


//  ����� �����������
    $str1 = 'abbr class=" icon-abbr" title="�����:';
    $str2 = '">';
    $pre_val = get_val(); // �.�. �������� ���������� � ��������
    $pre_val = str_replace( $str1, null, $pre_val );
    $pre_val = str_replace( $str2, null, $pre_val );
    $wind_direction = wind_shorter($pre_val).'<br/>';
//    array_push($res_out, $res['date']);
//    array_push($res_out, $daypart.$res['wind_direction']);

//  ����� ��������
    $str1 = '<span class="weather-table__wind">';
    $str2 = '</span>';
    $res['wind'] = get_val().' �/�';
    array_push($res_out, $res['date']);
    array_push($res_out, $daypart.$wind_direction.$res['wind']);
   }

echo "<pre>";
print_r($res_out);
echo "</pre>";



    // ������������ JavaScript ������
    while( /*( $ERROR <= 1 ) && */ ($val1 = array_shift($res_out)) )
    {
      $val2 = array_shift($res_out);
      // ������� ������ ������� ��������
      if ( strpos($val2,'���<br/>')===false ) continue;
      $x++;
      // ��� ������ ��������� �������� ������� ��� � ��������
      if( $x == 1 ) $result_js.= "var arr_date = [];\r\nvar arr_data = [];";
      // �������� ����� �����
      $val2 = str_replace('���<br/>', null, $val2 );
      $result_js.= "\r\narr_date[$x]='$val1';";
      $result_js.= "\r\narr_data[$x] = '$val2';";
    }
    $p1 = $p2;
  }

  echo "<pre>\r\nresult_js =\r\n".$result_js."</pre>";

?>