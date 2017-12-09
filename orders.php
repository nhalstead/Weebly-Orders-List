<?php
$file = "orders-[SHOP_NAME]-weebly-com-[START]-[END].csv";
date_default_timezone_set("EST");
$explain = true;
$catch = array("BILLING NAME", "TOTAL", "DATE", "SHIPPING EMAIL", "ORDER #");

$catchIndex = array();
$catchNUM = array();
$list = array();
$row = 1;
$empty = 0;
$Tnum = 0;
header("X-Powered-By: Noah 2.0");

// @link https://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
function iarray(){
	global $catchIndex, $catchNUM;
	return array_combine($catchIndex, $catchNUM);
}

// Loop the File to find the Records and the Data.
	if (file_exists($file) && ($handle = fopen($file, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$num = count($data);

			// Grab the Locations of the Columns
			if($row == 1){
				foreach($data as $k => $d){
					if(in_array(strtoupper($d), $catch)){
						$catchIndex[] = strtoupper($d);
						$catchNUM[] = $k;
					}
				}
			}
			else {
				$h = iarray();
				if( !empty( $data[ $h['TOTAL'] ] ) ){
					$list[] = array(
						"Name" => strtoupper( $data[ $h['BILLING NAME'] ] ),
						"Total" => strtoupper( $data[ $h['TOTAL'] ] ),
						"Date" => strtoupper( $data[ $h['DATE'] ] ),
						"OrderNum" => strtoupper( $data[ $h['ORDER #'] ] )
					);
				}
				else {
					$empty++;
				}
			}
			$row++;
			$Tnum += $num;
		}
		fclose($handle);
	}
	else {
		http_response_code(500);
		echo "File not Found!";
		exit();
	}

// Print the Header File Data
	echo "<title> Export of: ".$file."</title>";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=yes'>";
	echo <<<EOF
<style>
	@page{
		margin: 10px;
	}
</style>
EOF;
	$re = array(
		"File Name:" => $file,
		"Total Processed Rows of Data:" => count($list),
		"Date of Index Creation:" => date("F j @ Y, g:i A"),
		"File Creation Time:" => date("F j, Y @ g:i A", filectime($file))." (". time_elapsed_string('@'.filectime($file)) .")",
		"&nbsp;" => "&nbsp;",
		"<small>File Hash:</small>" => "<small>".md5_file($file)."</small>",
		"<small>Rows considered to be Empty:</small>" => "<small>".$empty."</small>",
		"<small>Total Read Calls:</small>" => "<small>".$Tnum."</small>",
	);
	echo "<table>";
	echo "<tr style=''><td style='width:300px'></td><td></td></tr>";
	foreach($re as $k => $s){
		echo "<tr>";
			echo "<td>".$k."</td><td><b>".$s."</b></td>";
		echo "</tr>";
	}
	echo "</table>";


// Loop the Records
	$records = array();
	foreach($list as $i => $k){
		$records[$k['Name']][$k['OrderNum']] = $k['Total'];
	}

// Print Records
	echo "<br>";
	echo "<table style='font-family: monospace; font-size: 15px;'>";
	echo "<tr style='background-color:darkorange;'><td style='width:450px; padding-left:4px;'>Name</td><td style='width:220px; padding-left:4px;'>Totals</td></tr>";
	foreach($records as $name => $r){
		$rr = array_sum($r);
		$h = "#aef1f1";
		$h = ($rr >= 94)?"yellow":$h;
		$h = ($rr >= 100)?"lightgreen":$h;
		if($explain == false){
			echo "<tr>";
				echo "<td>".$name."</td><td style='background-color:".$h."'>$".$rr."</td>";
			echo "</tr>";
		}
		else {
			echo "<tr>";
				echo "<td style='background-color:#d3eaae;padding-left:14px;'>".$name."</td><td></td>";
			echo "</tr>";
			
			foreach($r as $i => $p){
				echo "<tr>";
					echo "<td style='text-align: right;color: darkgrey;padding-right: 60px;'>".$i."</td><td style='background-color:#d4d0d0;padding-left:20px;'>$".$p."</td>";
				echo "</tr>";
			}
			
			echo "<tr>";
				echo "<td style='text-align:right;padding-right:10px;'>Total</td><td style='background-color:".$h.";padding-left:20px;'>$".$rr."</td>";
			echo "</tr>";
			echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
		}
	}
	echo "</table>";
// End of File.
?>