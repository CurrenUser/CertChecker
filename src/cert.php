<?php

set_error_handler(function($errno, $errstr, $errfile, $errline){
    http_response_code(500);
	echo $errno .' '. $errstr .' '. $errfile .' '. $errline;
	exit;
});

function GetCert($url) {
			$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false
				)
			)
		);
		 
		$fp = stream_socket_client($url, $err_no, $err_str, 30, STREAM_CLIENT_CONNECT, $context);
		$cert = stream_context_get_params($fp);
		 
		if (empty($err_no)) {
			$info = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
			$domain = $info['subject']['CN'];
			$signatory = $info['issuer']['CN'];
			$published = date('d.m.Y', $info['validFrom_time_t']);
			$expires = date('d.m.Y', $info['validTo_time_t']);
			$curent_date = date('d.m.Y');
			$expiration = strtotime($expires) - strtotime($curent_date);
			return json_encode( 
				array( 
					'domain' => $domain, 
					'signatory'  => $signatory,
					'published' => $published,
					'expires' => $expires,
					'curent_date' => $curent_date,
					'expiration' => $expiration / 86400
					) 
			);
		}
}

function CheckCert( $url ) {
	try{
		$json = GetCert($url);
		http_response_code(201);
		header('Content-Type: application/json');
		echo $json;
	}catch (Exception $error) {
		http_response_code(500);
		echo $error->getMessage();
	}
restore_error_handler();
}

function Monitor() {
	try {
		sleep(5);
	    echo '<style>
	   .red {
	    background-color: #FA8072; 
	   }

	   .green {
	    background-color: #90EE90; 
	   }

	   td {
	   	text-align: center;
	   	border: 1px solid black;
	   }

	   </style>';
		echo '<body><table style="margin: 0 auto; border: solid 2px black; border-collapse: collapse;">';
		$urls = explode(" ", file_get_contents('monitor.conf'));
		foreach ($urls as $url) {
			$str = GetCert('ssl://'.$url.':443');
			$json = json_decode($str);

		if ($json->{'expiration'} < 30 )
			echo '<tr class="red">';
		else
			echo '<tr class="green">';
				echo '<td colspan="2"> url:'. $url ."</td>";
				echo '<td colspan="2"> domain:'. $json->{'domain'} ."</td>";
				echo '<td colspan="2"> signatory:'. $json->{'signatory'} ."</td>";
				echo '<td colspan="2"> expiration:'. $json->{'expiration'} ."</td>";
			echo "</tr>";
		}
		echo "</body</table>";
	}catch (Exception $error) {
		http_response_code(500);
		echo $error->getMessage();
	}
}

function MonitorJSON() {

	$responce = array();

	try {
	$urls = explode(" ", file_get_contents('monitor.conf'));

		foreach ($urls as $url) {
			$str = GetCert('ssl://'.$url.':443');
			$json = json_decode($str);
			array_push( $responce, array( $url => array( 
				$json->{'domain'}, 
				$json->{'signatory'}, 
				$json->{'expiration'} ) 
		   ));
		}
		
		http_response_code(201);
		header('Content-Type: application/json');
		echo(json_encode($responce));

	}catch (Exception $error) {
		http_response_code(500);
		echo $error->getMessage();
	}
}

switch ( $_SERVER['REQUEST_METHOD'] ) {
	case 'GET':
	http_response_code(201);
     if (isset($_GET["url"]))
	    CheckCert('ssl://'.htmlspecialchars($_GET["url"]).':443');
	 elseif (isset($_GET["monitor"]))
        Monitor();
    elseif (isset($_GET["monitor_json"]))
        MonitorJSON();
	 else
		echo 'for geting info of cert (?url=site_name)<br> for geting info from file (?monitor)<br>';
	break;
}

?>