<?php
/**
 * Created by PhpStorm.
 * User: kosmos
 * Date: 03/05/2018
 * Time: 10:59
 */

class proz
{
    public $access_token;
    public $pair1 = '';
    public $pair2 = '';
    public $form  = '';
    public $table = '';
    public $limit_select = '';
    public $language_service = '';


    public function __construct() {

        $this->authentication();

        //grab methods
        $this->grabAccount_type();


        $this->getLanguages();

        $this->getServices();

        //$this->sendMessage();

        $this->show_form();
        $this->getServices();




//        if(isset($_REQUEST['pair1']) && isset($_REQUEST['pair2'])) {
//            $this->get_list($_REQUEST['pair1'],$_REQUEST['pair2']);
//        }
    }


    public function sendMessage($rows,$pair1,$pair2) {

        $servername = "localhost";
        $username = "root";
        $password = "password";
        $dbname = "proz";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


        $array = array();
        for($i=0;$i<count($rows);$i++) {

            $uuid= $rows[$i]->freelancer->uuid;

            $array[] = $uuid;

            $sql = "SELECT id FROM messages WHERE uuid = '".$uuid."'";
            $result = $conn->query($sql);


            if ($result->num_rows == 0) {
                $sql = "INSERT INTO messages (uuid, pair1, pair2,send_time) VALUES ('" . $uuid . "', '" . $pair1 . "', '" . $pair2 . "', NOW())";

                if ($conn->query($sql) === TRUE) {
                    $this->curlSend($uuid);
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }


        }
        $conn->close();


        //die;

    }

    public function curlSend($uuid) {

        echo "<pre>";
        print_r($uuid);
        echo "</pre>";

        $curlHandle = curl_init();

        curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/messages");
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));


        $body = "Hello,\n
Effectiff Services is looking for Cantonese and Mandarin over the phone interpreters with knowledge of medical terminology to work on a large project. If you are interested please email favio.estevez@effectiff.com\n 
Thanks in advance,\n\n
Regards,\n 
Favio Estevez.
";

        $post_array = array('recipient_uuids'=>''.$uuid.'','sender_name'=>'Favio Estevez','sender_email'=>'favio.estevez@effectiff.com','subject'=>'looking for Cantonese and Mandarin over the phone interpreters ','body'=>''.$body.'');
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post_array);

        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 201) {
            die("worked!!!  unexpected response ".$response);
        }
        $row = json_decode($response);

        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }

    public function getServices() {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/codes/language-service");

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);

        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 200) {
            die("unexpected response ".$response);
        }

        $rows = json_decode($response);


        $this->language_service .= '<select name="language_service">';
        $this->language_service .= '<option value="">--выберите сервис--</option>';
        foreach ($rows->language_services as $k=>$v) {

            $selected = '';
            if(isset($_REQUEST['language_service']) && $_REQUEST['language_service'] == $v->lang_service_id) {
                $selected = "selected";
            }

            $this->language_service  .= "<option value=\"".$v->lang_service_id."\" $selected>".$v->lang_service_name."</option>";
        }
        $this->language_service  .= '</select>';



//        echo "<pre>";
//        print_r($this->pair2); die;

    }



    public function authentication() {

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, "https://www.proz.com/oauth/token");
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        curl_setopt($curlHandle, CURLOPT_USERPWD, "d3c666a86be089a8d9a14e36a512d4188bf9a7d0:c9365b0b8810e78db5496d9be709612bb5a4915b");

        $post_array = array('grant_type'=>'client_credentials','scope'=>'message.send');
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post_array);

        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 200) {
            die("unexpected response ".$response);
        }

        $row = json_decode($response);
        $this->access_token = $row->access_token;

//        echo "<pre>";
//        print_r($row); die;

    }

    public function getLanguages() {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/codes/language");

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);

        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 200) {
            die("unexpected response ".$response);
        }

        $rows = json_decode($response);


        $this->pair1 .= '<select name="pair1">';
        $this->pair1 .= '<option value="">--выберите язык--</option>';
        foreach ($rows->languages as $k=>$v) {

            $selected = '';
            if(isset($_REQUEST['pair1']) && $_REQUEST['pair1'] == $v->language_code) {
                $selected = "selected";
            }

            $this->pair1  .= "<option value=\"".$v->language_code."\" $selected>".$v->language_name."</option>";
        }
        $this->pair1  .= '</select>';

        $this->pair2 .= '<select name="pair2">';
        $this->pair2 .= '<option value="">--выберите язык--</option>';
        foreach ($rows->languages as $k=>$v) {
            $selected = '';
            if(isset($_REQUEST['pair2']) && $_REQUEST['pair2'] == $v->language_code) {
                $selected = "selected";
            }
            $this->pair2  .= "<option value=\"".$v->language_code."\" $selected>".$v->language_name."</option>";
        }
        $this->pair2  .= '</select>';

//        echo "<pre>";
//        print_r($this->pair2); die;

    }

    public function limit_show() {

        if(isset($_REQUEST['limit']) && $_REQUEST['limit']) {
            $limit = $_REQUEST['limit'];
        }
        else {
            $limit = 10;
        }

        $limit = $_REQUEST['limit'];

            $this->limit_select .= '<select name="limit">';
            $this->limit_select .= '<option value="10" '.($limit==10)?"selected":"".'>10</option>';
            $this->limit_select .= '<option value="20" '.($limit==20)?"selected":"".'>20</option>';
            $this->limit_select .= '<option value="50" '.($limit==50)?"selected":"".'>50</option>';
            $this->limit_select .= '<option value="100" '.($limit==100)?"selected":"".'>100</option>';
            $this->limit_select  .= '</select>';

        return $this->limit_select;

    }

    public function show_form() {
        $this->form .= '<form action="index.php" method="post">';

            $this->form .= '<div>';
                $this->form .= 'Языковая пара: <br /><br />'.$this->pair1.' - '.$this->pair2.'<br><br />';
            $this->form .= '</div>';

        $this->form .= '<div>';
            $this->form .= $this->language_service;
        $this->form .= '</div><br /><br />';

            $this->form .= '<div>';
                //$this->form .= $this->limit_show();
            $this->form .= '</div>';

            $this->form .= '<div>';
                $this->form .= '<input type="submit" value="Поиск"><br/><br />';
            $this->form .= '</div>';

            $this->form .= '<div>';
                if(isset($_REQUEST['pair1']) && isset($_REQUEST['pair2'])) {
                    $this->form .= $this->get_list($_REQUEST['pair1'],$_REQUEST['pair2']);
                }
            $this->form .= '</div>';



        $this->form .= '</form>';

        echo $this->form;
    }

    public function get_list($pair1,$pair2) {

        if(isset($_REQUEST['offset']) && $_REQUEST['offset']) {
            $offset = $_REQUEST['offset'];
        }
        else {
            $offset = 0;
        }

        if(isset($_REQUEST['limit']) && $_REQUEST['limit']) {
            $limit = $_REQUEST['limit'];
        }
        else {
            $limit = 100;
        }

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/freelancer-matches?limit=".$limit."&offset=".$offset."&language_pair=".$pair1."_".$pair2);

        //curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/freelancer-matches?country_code=by&limit=".$limit."&offset=".$offset."&language_pair=".$pair1."_".$pair2);

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
//        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);

        //$post_array = array('language_pair'=>$pair1.'_'.$pair2);

//        echo "<pre>";
//        print_r($post_array); die;

//        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post_array);

        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 200) {
            die("unexpected response ".$response);
        }

        $rows=json_decode($response);

        //$this->sendMessage($rows->data,$pair1,$pair2);


        echo "<pre>";
        print_r($rows); die;


        $this->table .= '<div>Всего результатов:'.$rows->meta->num_results.'</div>';

        $this->table .= '<table border="1" cellpadding="10" cellspacing="0">';

        $this->table .= '<tr>';
            $this->table .= '<th>#</th>';
            $this->table .= '<th>image_url</th>';
            $this->table .= '<th>site_name</th>';

            $this->table .= '<th>profile_url</th>';
        $this->table .= '</tr>';

//        echo "<pre>";
//        print_r($rows);

//
        $i=1;
        foreach($rows->data as $k=>$v) {
            $this->table .= '<tr>';
            $this->table .= '<td>'.$i++.'</td>';
            $this->table .= '<td><img src="'.$v->freelancer->image_url.'" width="100"></td>';
                $this->table .= '<td>'.$v->freelancer->site_name.'</td>';
                $this->table .= '<td><a href="'.$v->freelancer->profile_url.'" target="_blank ">'.$v->freelancer->profile_url.'</a></td>';
            $this->table .= '</tr>';
        }
        $this->table .= '</table>';


        return $this->table;

    }

    public function grabAccount_type() {

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "proz";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }


        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, "https://api.proz.com/v2/codes/account-type");
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
        $response = curl_exec($curlHandle);

        if($response == FALSE) {
            $errorText = curl_error($curlHandle);
            curl_close($curlHandle);
            die($errorText);
        }

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        if($httpCode != 200) {
            die("unexpected response ".$response);
        }

        $rows=json_decode($response);

        $types= $rows->account_types;

        for($i=0;$i<count($types);$i++) {

            $sql = "INSERT INTO account_type (account_type_id, account_type_name) VALUES (" . $types[$i]->account_type_id . ", '" . $types[$i]->account_type_name ."')";

            if ($conn->query($sql) === TRUE) {

            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        echo "<pre>";
        print_r($rows); die;

    }


}

$var = new proz();