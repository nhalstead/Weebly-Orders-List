<?php
header("Content-Type: text/plain");
$file = "orders-[SHOP_NAME]-weebly-com-[START]-[END].csv";

$catch = array("billing name", "total", "order notes", "date", "shipping email");
$catchNUM = array();
$list = array();

$row = 1;
$empty = 0;

function iarray(){
	global $catch, $catchNUM;
	return array_combine($catch, $catchNUM);
}

if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
		
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
    }
    fclose($handle);
}

echo "Empty Rows: ".$empty.PHP_EOL;
//print_r($list);

$records = array();
foreach($list as $i => $k){
	$records[$k['Date']][] = $k['Total'];
}

//print_r($records);

foreach($records as $i => $r){
	echo $i."\t\t\t\t|\t\t$".array_sum($r)."\r";
}

?>