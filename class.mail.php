<?php

class Mail {
	private $body = null;

	public function setBody($body) {
		$this->body = $body;
	}

	public function send($to, $subject) {
		$url = 'https://api.sendgrid.com/';
		$user = 'steamdispenser';
		$pass = 'Henkie45';
		$json_string = array(

		  'to' => array(
		    $to
		  ),
		  'category' => 'Email'
		);
		$params = array(
		    'api_user'  => $user,
		    'api_key'   => $pass,
		    'x-smtpapi' => json_encode($json_string),
		    'to'        => $to,
		    'subject'   => "Someone sent you a file! - ".$subject,
		    'html'      => $this->body,
		    'text'      => $this->body,
		    'from'      => 'no-reply@steamdispenser.com',
		  );
		$request =  $url.'api/mail.send.json';
		$session = curl_init($request);
		curl_setopt ($session, CURLOPT_POST, true);
		curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($session);

		if(curl_errno($session)) {
		    die(CUSTOMERSERVICE);
		}

		curl_close($session);
		return true;
	}
}