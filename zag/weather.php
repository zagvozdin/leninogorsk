<?
  $result_js = null;
  include 'weather_yandex.php';
  if ($result_js == null) die('no data');
//  include 'data.ini';

$text = '...';
//$text = iconv('utf-8','windows-1251', file_get_contents('C:\Distrib\weather\text.txt'));

$f1 = fopen('./text.txt','r');
if ( $f1 )
{
  $text = fread($f1,512); 
//  $text = iconv('UTF-8','WINDOWS-1251',$text);
  fclose($f1);
}



$speed = 50;
$f2 = fopen('./text_speed.txt','r');
if ( $f2 )
{
  $text_speed = fread($f2,512); 
  fclose($f2);
}


  $path = './';
  $name_dst = 'weather.html';
  $name_src = $name_dst.'.tmp';

  $f = fopen($path.$name_src,'w+');
  if ( !$f ) die('file open error');

  fputs($f,"<html>
<meta http-equiv='Content-Type' content='text/html; charset=windows-1251'/>
<meta http-equiv='refresh' content='3900'/>
<title>Прогноз погоды на три дня</title>
<link rel='stylesheet' href='./style/style.css'/>
<script type='text/javascript' src='./jquery/jquery.min.js'></script>
<body>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<table class='outer' width=98 height=98>
<tr><td>
<div id=weather class='inner_upper'>...</div>
<div id='data' class='inner_lower'><marquee scrolldelay='$text_speed'>$text</marquee></div>
</td></tr>
</table>
<script type='text/javascript'>
$result_js
i = 1;
setInterval( function() { $('#weather').html(arr_date[i]+'</br>'+arr_data[i]); i++; if( i>=arr_date.length ) i = 1; } , 3000 );
</script>
</body>
</html>"); 

copy($path.$name_src, $path.$name_dst);

?>