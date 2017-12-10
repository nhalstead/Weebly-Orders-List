<?php
$file = "orders-[SHOP_NAME]-weebly-com-[START]-[END].csv";

date_default_timezone_set("EST");
$explain = true;
$catch = array("BILLING NAME", "TOTAL", "DATE", "SHIPPING EMAIL", "ORDER #", "TAX TOTAL", "SHIPPING PRICE", "SHIPPING COUNTRY", "SHIPPING REGION");

$catchIndex = array();
$catchNUM = array();
$list = array();
$row = 1;
$empty = 0;
$Tnum = 0;
define("DATE", "F j, Y @ g:i A");

// File Selector
if( (!isset($file) || !file_exists($file)) && !isset($_GET['file'])){
	$file = "";
	$fs = glob("*.csv");
	
	usort($fs, function($a,$b){
	  return filemtime($a) - filemtime($b);
	});
	$fs = array_reverse($fs);
	
	$fslist = array();
	foreach($fs as $i => $name){
		$fslist[] = $name;
	}
	
	echo "<table>";
		echo "<tr><td style='width:480px;'>&nbsp;</td><td style='width:300px;'>&nbsp;</td><td>&nbsp;</td></tr>";
		foreach($fslist as $i => $fs){
			echo "<tr><td><a href='?file=".$fs."'>".$fs."</a></td><td>".md5_file($fs)."</td><td>" .time_elapsed_string('@'.filectime($fs)). "</td></tr>";
		}
	echo "</table>";
	exit();
}
else if(isset($_GET['file']) && preg_match("/orders-([a-zA-Z0-9]{1,})-weebly-com-([0-9]{1,}|start)-([0-9]{1,})\.csv/", $_GET['file'])) {
	$file = $_GET['file'];
}

preg_match("/orders-([a-zA-Z0-9]{1,})-weebly-com-([0-9]{1,}|start)-([0-9]{1,})\.csv/", $file, $matches);
if($matches[2] === "start"){
	$matches[2] = 0;
}
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
						"Name" => strtoupper( $data[$h['BILLING NAME']] ),
						"Total" => strtoupper( $data[$h['TOTAL']] ),
						"Date" => strtoupper( $data[$h['DATE']] ),
						"OrderNum" => strtoupper( $data[$h['ORDER #']] ),
						"Tax" => strtoupper( $data[$h['TAX TOTAL']] ),
						"Shipping" => strtoupper( $data[$h['SHIPPING PRICE'] ] ),
						"Region" => strtoupper( $data[$h['SHIPPING COUNTRY']] . $data[$h['SHIPPING REGION']] )
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
		echo "File not Found!<br>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;".$file;
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
		"Date From: " => date(DATE, $matches[2]),
		"Date To: " => date(DATE, $matches[3]),
		"Total Processed Rows of Data:" => count($list),
		"&nbsp;" => "&nbsp;",
		"<small>File Name:</small>" => "<small>".$file."</small>",
		"<small>Date of Index Creation:</small>" => "<small>".date(DATE)."</small>",
		"<small>File Creation Time:</small>" => "<small>".date(DATE, filectime($file))." (". time_elapsed_string('@'.filectime($file)) .")</small>",
		"<small>File Hash:</small>" => "<small>".md5_file($file)."</small>",
		"<small>Rows considered to be Empty:</small>" => "<small>".$empty."</small>",
		"<small>Total Read Calls:</small>" => "<small>".$Tnum."</small>",
	);
	echo "<table style='font-size:15px;'>";
	echo "<tr><td style='width:300px;'></td><td></td></tr>";
	foreach($re as $k => $s){
		echo "<tr>";
			echo "<td>".$k."</td><td><b>".$s."</b></td>";
		echo "</tr>";
	}
	echo "</table>";


// Loop the Records
	$records = array();
	$total = 0;
	$tax = 0;
	$shipping = 0;
	foreach($list as $i => $k){
		$records[$k['Name']][$k['OrderNum']] = $k['Total'];
		$shipping += $k['Shipping'];
		$total += $k['Total'];
		$tax += $k['Tax'];
	}

// Print Records
	echo "<br>";
	echo "<table style='font-family: monospace; font-size: 14px;'>";
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
	
	echo "<table style='margin-left:30px;padding:6px;border: 2px solid black;'>";
		echo "<tr><td>Total Shipping:</td><td>$". sprintf('%01.2f', $shipping) ."&nbsp;&nbsp;&nbsp;&nbsp;<small>( Already in the Order Price )</small></td></tr>";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
		echo "<tr><td>Total Tax:</td><td>$". sprintf('%01.2f', $tax) ."</td></tr>";
		echo "<tr><td>Sub Total:</td><td><b>$". sprintf('%01.2f', $total) ."</b></td></tr>";
		echo "<tr><td style='width:150px;'>&nbsp;</td><td style='width:300px;'>&nbsp;</td></tr>";
		echo "<tr><td>Total:</td><td>$". sprintf('%01.2f', (($total-$tax)-$shipping) ) ."&nbsp;&nbsp;&nbsp;&nbsp;<small>( This has the Tax Subtracted and the Shipping )</small></td></tr>";
	echo "</table>";
	echo "<br><br><br>";
// End of File.
?>