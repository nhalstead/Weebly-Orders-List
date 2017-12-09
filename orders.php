<?php
$file = "orders-[SHOP_NAME]-weebly-com-[START]-[END].csv";

$catch = array("billing name", "total", "order notes", "date", "shipping email");
$catchNUM = array();
$list = array();

$row = 1;
$empty = 0;
$Tnum = 0;

function iarray(){
	global $catch, $catchNUM;
	return array_combine($catch, $catchNUM);
}

if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);

		// Grab the Locations of the Columns
		if($row == 1){
			foreach($data as $k => $d){
				if(in_array(strtolower($d), $catch)){
					$catchNUM[] = $k;
				}
			}
		}
        else {
			$h = iarray();
			if( !empty( $data[ $h['total'] ] ) ){
				$list[] = array(
					"Name" => strtoupper( $data[ $h['billing name'] ] ),
					"Total" => strtoupper( $data[ $h['total'] ] ),
					"Date" => strtoupper( $data[ $h['date'] ] )
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

echo "File Name: <b>".$file."</b><br>";
echo "File Hash: <b>".md5_file($file)."</b><br>";
echo "Rows considered to be Empty: ".$empty."<br>";
echo "Total Read Calls: ".$Tnum."<br>";

echo "Total Processed Rows of Data: <b>".count($list)."</b><br>";

$records = array();
foreach($list as $i => $k){
	$records[$k['Date']][] = $k['Total'];
	echo ".";
}

echo "<br>";

echo "<table>";
echo "<tr style='background-color: darkorange;'><td style='width:400px'>Name</td><td style='width:200px'>Totals</td></tr>";
foreach($records as $i => $r){
	$rr = array_sum($r);
	$h = "white";
	$h = ($rr >= 90)?"yellow":$h;
	$h = ($rr >= 100)?"lightgreen":$h;
	echo "<tr>";
		echo "<td>".$i."</td><td style='background-color:".$h."'>$".$rr."</td>";
	echo "</tr>";
}
echo "</table>";

?>