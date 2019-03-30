<?php
error_reporting(0);
header('Access-Control-Allow-Origin: *');
$data = array();
$smsdata = array();
include('dbConnect.php');


  function sendSMS($mobile,$text) {
	  

      $username = 'ashda';
      $password = 'ashda123';
      $sender_id    = 'ASHDAS';
      $text = 'thank you for join typ.';

      $content =  'loginID='.rawurlencode($username).
                '&password='.rawurlencode($password).
                '&mobile='.rawurlencode($mobile).
                '&senderid='.rawurlencode($sender_id).
                '&text='.rawurlencode($text).
                '&route_id=2'.
                '&Unicode=0';
				

       $ch = curl_init('http://198.24.149.4/API/pushsms.aspx?'.$content);
       curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
       curl_setopt($ch, CURLOPT_TIMEOUT, 5);
       $output = curl_exec ($ch);
       curl_close ($ch);
       $smsglobal_response = $output;

       //Sample Response
       //{"LoginStatus":"Success","Balance":"9994","BalanceStatus":"OK","ValidNumbers":"0","SMSCount":"0","MsgStatus":"failed","CurrentBalance":"9994","Transaction_ID":""}
       $explode_response = json_decode($smsglobal_response);
       if($explode_response->MsgStatus == "Sent") { //Message Success
           $Transaction_ID = $explode_response->Transaction_ID;
           $CurrentBalance = $explode_response->CurrentBalance;
			
			array_push($smsdata,array('status'=>true,'Transaction_ID'=>$Transaction_ID,'mobile'=>$mobile,'username'=>$username,'CurrentBalance'=>$CurrentBalance));
            //print_r(array('status'=>true,'Transaction_ID'=>$Transaction_ID,'mobile'=>$mobile,'username'=>$username,'CurrentBalance'=>$CurrentBalance));
       }else{ //Message Failed
           if($explode_response->ValidNumbers == 0){
             $error = "Invalid Mobile Number";
           }
           if($explode_response->LoginStatus != "Success"){
             $error = "Invlaid Login Status";
           }
           if($explode_response->Balance <= 0 && $explode_response->BalanceStatus != "OK"){
             $error = "influence balance";
           }

           print_r(array('status'=>false,'error'=>$error,'msg'=>'Message Failed.'));
       }
  }//over sendsms function

 
 

if(isset($_POST['type']))
{
		$check = 0;
		$phonelist="";
	    $type = $_POST['type'];
	    $tags = $_POST['tags'];
	  $pl=array();
		$mlist = " ( ";
		$tag_arr = explode(",",$tags);
		$cnt_dd = count($tag_arr);
		$i = 1;
		foreach($tag_arr as $tt)
		{
			$dd = explode("_",$tt);
			$cout = count($dd);
			if($cout == 2)
			{
				if($i < $cnt_dd){
					$mlist .= $dd[1].",";
				}else{
					$mlist .= $dd[1];
				}
				array_push($pl, $dd[0]);
			}else{
			array_push($pl, $dd[0]);
								
			 }
		
			$i++;
		}
		$mlist .= " ) ";
	
  		if($type=="allmembers")
  		{
			
			$sql = "SELECT ID,user_login FROM wp_users order by ID";
		}else if($type=='any'){
			 $sql = "SELECT ID,user_login FROM wp_users WHERE ID IN ".$mlist." "; 
			$text = $_POST['text'];
			$j=0;
			foreach($pl as $tt)
			{
				 //sendSMS($tt,$text);
				 //echo $tt;
				$j++;
			}	 
			$check = 1;
		  }else{
			$data['responceCode'] = 'ERROR';
			$data['responceFlag'] = false;
			$data['responceError'] = 'Invalid Media Data!';
			$data['responceResult'] = '';
			echo json_encode($data);
			exit;
		  }
			
			
				$rows = array();
				$checkmember = 0;
				$res = mysqli_query($con, $sql);
				$num = mysqli_num_rows($res);
				
				$rows = array();
			
				if($num >= 1)
				{
					while($row=mysqli_fetch_assoc($res)){
					array_push($rows,$row);
					}
					$data['responceCode'] = 'SUCCESS';
					$data['responceFlag'] = true;
					$data['responceSize'] = count($rows);
					$data['responceResult'] = $rows;
					$data['phone']=json_encode($pl);
					$data['type']=$type;
					
					$checkmember = 1;
				}
				else
				{
					$data['responceCode'] = 'ERROR';
					$data['responceFlag'] = false;
					$data['responceError'] = 'Not_found11';
					$data['responceResult'] = json_encode($pl);
					$data['phone']=json_encode($pl);
					$data['type']=$type;
				}
			
			

}else{
  $data['responceCode'] = 'ERROR';
  $data['responceFlag'] = false;
  $data['responceError'] = 'Invalid Media Type!';
  $data['responceResult'] = '';
}

echo json_encode($data);
?>
