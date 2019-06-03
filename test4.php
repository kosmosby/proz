<?php


$input_line = file_get_contents('list.csv');
//$input_line = file_get_contents('list4.htm');


//preg_match_all("/href=\".*[0-9]\">(.*)<\/a><\/td>\s*<td class=\"website\">(.*)<\/td>/", $input_line, $output_array);
//preg_match_all("/.*website\"><a href=\"(.*\/)\".*/", $input_line, $output_array);
preg_match_all("/([A-Za-z0-9._%+-]{0,4}@[A-Za-z0-9.-]+\.[A-Za-z]{2,6})/",$input_line,$output_array);

//preg_match_all("/object_link\"\s*href=\"(.*)\"\>/",$input_line,$output_array);



//echo "<pre>";
//print_r($output_array); die;

//$csv_array = array();
//$i=0;


//foreach($output_array[1] as $k=>$v) {
//
//    if($k>=2000 && $k<2500) {
//        $input_line_link = file_get_contents($v);
//        preg_match_all("/le\"\s*data-item-id=\"{IID}\"\>(.*)\s(.*)\<\/h2\>.*<td>(.*)<\/td>/", $input_line_link, $output_array_link);
//        $csv_array[$i]['first_name'] = $output_array_link[1][0];
//        $csv_array[$i]['last_name'] = $output_array_link[2][0];
//        $csv_array[$i]['company'] = $output_array_link[3][0];
//        $i++;
//    }
//    if($k>2500) {
//        break;
//    }

//    echo "<pre>";
//    print_r($csv_array); die;
//}




$data = '';
//for($i=0;$i<count($csv_array);$i++) {
//    $data .= str_replace("&amp;","",$csv_array[$i]['first_name']) .";".str_replace("&amp;","",$csv_array[$i]['last_name']).";".str_replace("&amp;","",$csv_array[$i]['company'])."\n";
//}


//for($i=0;$i<count($output_array[2]);$i++) {
//
//    preg_match("/href=\"(.*)\" target/", $output_array[2][$i], $output_array2);
//    if(isset( $output_array2[1])) {
//        $output_array[2][$i] = $output_array2[1];
//    }
//}
//
for($i=0;$i<count($output_array[1]);$i++) {
    $data .= $output_array[1][$i]."\n";
    //$data .= str_replace("&amp;","",$output_array[1][$i]). " ".str_replace("&nbsp;","",$output_array[2][$i]).";".str_replace("&amp;","",$output_array[3][$i]).";".str_replace("&amp;","",$output_array[4][$i])."\n";
}


header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="filename.csv"');
echo $data; exit();


?>


