<?php
namespace App;

class Test extends Entity {

	public function mail() {
		if (!isset($_GET['to'])) {
			$this->result['error'] = "Need 'to' parameter !";
			return;
		}
		require_once 'lib/swift_required.php';
		$subject = 'Test';
		$msg = '<b>Hello</b>, this is a test !';
		$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
						->setUsername('cesi@kolapsis.com')
						->setPassword('rPvTGCbGmvB9rxdC');
		$mailer = \Swift_Mailer::newInstance($transport);
		$message = \Swift_Message::newInstance()
						->setSubject($subject)
						->setFrom(array('cesi@kolapsis.com' => 'CESI'))
						->setTo(array($_GET['to']))
						->setBody($msg, 'text/html')
						->addPart(strip_tags($msg), 'text/plain');
		$success = $mailer->send($message);
		if ($success != 1) $this->result['error'] = "Could not send mail !";
	}

}