<?php

function send_email($from, $to, $subject, $message){

	// Helper function for sending email
	include("phpmailer/class.phpmailer.php");  // 匯入PHPMailer library
	 
	$mail= new PHPMailer();          //建立新物件	
	$mail->IsSMTP(); //設定使用SMTP方式寄信 
	$mail->SMTPAuth = true; //設定SMTP需要驗證 
	$mail->SMTPSecure = "ssl"; // Gmail請服用 
	$mail->Host = "smtp.gmail.com"; //Gmail請服用 
	// $mail->Host = 'ssl://smtp.gmail.com:465'; 
	// $mail->Host = "這邊是smtp"; //Gamil的SMTP主機 
	$mail->Port = 465; //Gamil的SMTP主機的SMTP埠位為465埠。 
	$mail->CharSet = "utf-8"; //設定郵件編碼 
	$mail->Encoding = "base64";
	$mail->FromName = "Bryce";
	$mail->Username = ''; //設定驗證帳號 
	$mail->Password = ""; //設定驗證密碼  
	$mail->From = ''; //設定寄件者信箱
	$mail->IsHTML(true); //設定郵件內容為HTML
	$mail->Subject = $subject;    //設定郵件標題
	$mail->Body = $message;  //設定郵件內容
	$mail->addAddress($to,'user');     // Add a recipient

	if(!$mail->send()) {
	    echo 'Message could not be sent.';
	    echo 'Mailer Error: ' . $mail->ErrorInfo;
	    return false;
	} else {
	    return true;
	}

	// $headers  = 'MIME-Version: 1.0' . "\r\n";
	// $headers .= 'Content-type: text/plain; charset=utf-8' . "\r\n";
	// $headers .= 'From: '.$from . "\r\n";

	// return mail($to, $subject, $message, $headers);
}

function get_page_url(){

	// Find out the URL of a PHP file

	$url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['SERVER_NAME'];

	if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != ''){
		$url.= $_SERVER['REQUEST_URI'];
	}
	else{
		$url.= $_SERVER['PATH_INFO'];
	}

	return $url;
}

function rate_limit($ip, $limit_hour = 20, $limit_10_min = 10){
	
	// The number of login attempts for the last hour by this IP address

	$count_hour = ORM::for_table('reg_login_attempt')
					->where('ip', sprintf("%u", ip2long($ip)))
					->where_raw("ts > SUBTIME(NOW(),'1:00')")
					->count();

	// The number of login attempts for the last 10 minutes by this IP address

	$count_10_min =  ORM::for_table('reg_login_attempt')
					->where('ip', sprintf("%u", ip2long($ip)))
					->where_raw("ts > SUBTIME(NOW(),'0:10')")
					->count();

	if($count_hour > $limit_hour || $count_10_min > $limit_10_min){
		throw new Exception('Too many login attempts!');
	}
}

function rate_limit_tick($ip, $email){

	// Create a new record in the login attempt table

	$login_attempt = ORM::for_table('reg_login_attempt')->create();

	$login_attempt->email = $email;
	$login_attempt->ip = sprintf("%u", ip2long($ip));

	$login_attempt->save();
}

function redirect($url){
	header("Location: $url");
	exit;
}