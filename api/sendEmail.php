<?php
	include "service.php";

	$sendEmailTimeFormat = "[Y-m-d H:i:s] ";
	$sendEmailTime = date ($sendEmailTimeFormat, time())."sendEmail: ";

	if(getNotificationActive()){
		$nbEmailSend = 0;
		echo $sendEmailTime."launch\n";
		$now = time();
		$rows = userListForEmail();
		foreach($rows as $row){
			if(sendMail($row['email'], $row['lang'], "reminder", null)){
				echo $sendEmailTime."email sended to ".$row['email']."\n";
				$nbEmailSend++;
				updateUserEmailSend($row['id'], $now);
			}
		}
		$sendEmailTime = date ($sendEmailTimeFormat, time())."sendEmail: ";
		echo $sendEmailTime.$nbEmailSend." email have been sent\n";
		echo $sendEmailTime."stop\n";
	}else{
		echo $sendEmailTime."Reminder option not active\n";
	}
	sqlClose();
?>