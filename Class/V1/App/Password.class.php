<?php
namespace App;

use Library\DB;
use Library\Entity;
use Library\Token;
use PDO;

class Password extends Entity {

	public function query() {
		ob_start();
        require 'Templates/password_query.php';
        echo ob_get_clean();
        exit;
	}

	public function request() {
		if (empty($_POST['email'])) {
			$this->result['error'] = 'Email could not be empty !';
			return;
		}
        $qry = "SELECT `u`.`id`, `u`.`name`, `u`.`email` FROM  `user` AS  `u` WHERE `u`.`email` = '" . $_POST['email'] . "' LIMIT 0 , 1";
        $rs = DB::query($qry);
        if ($rs && $rs->rowCount() == 1) {
            $user = $rs->fetch(PDO::FETCH_ASSOC);
			$user['recover_token']  = uniqid();
			$user['recover_expire'] = date("Y-m-d H:i:s", time() + (60 * 60));
			$qry = "UPDATE user SET recover_token='" . $user['recover_token'] . "', recover_expire='" . $user['recover_expire'] . "' WHERE id = " . $user['id'];
			if (DB::exec($qry)) {
				if (!$this->sendPasswordMail($user)) {
					$this->result['error'] = 'Send recovery password mail error';
					$qry = "UPDATE user SET recover_token=NULL, recover_expire=NULL WHERE id = " . $user['id'];
					DB::exec($qry);
				}
			} else {
				$this->result['error'] = 'Unable to define recovery token';
			}
		} else {
			$this->result['error'] = 'No user found for this email';
		}
	}

	private function sendPasswordMail($user) {
		require_once 'lib/swift_required.php';
		$user['subject'] = 'Récupération de votre mot de passe';
		$user['link'] = BASE_URL . strtolower(str_replace('App\\', '', get_class($this))) . '/change?token=' . $user['recover_token'];
		ob_start();
        require 'Templates/password_mail.php';
        $content = ob_get_clean();

        $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
						->setUsername('cesi@kolapsis.com')
						->setPassword('rPvTGCbGmvB9rxdC');
		$mailer = \Swift_Mailer::newInstance($transport);
		$message = \Swift_Message::newInstance()
						->setSubject($user['subject'])
						->setFrom(array('cesi@kolapsis.com' => 'CESI'))
						->setTo(array($user['email'] => $user['name']))
						->setBody($content, 'text/html')
						->addPart(strip_tags($content), 'text/plain');
		return $mailer->send($message);
	}

	public function change() {
		if (isset($_GET['finish']) && $_GET['finish'] == 1) {
			echo 'Merci, vous pouvez vous reconnecter à votre application.';
		} else if (empty($_GET['token'])) {
			echo 'Token is empty !';
		} else {
			$qry = "SELECT `u`.`id`, `u`.`name`, `u`.`recover_token` FROM  `user` AS  `u` WHERE `u`.`recover_token` = '" . $_GET['token'] . "' LIMIT 0 , 1";
	        $rs = DB::query($qry);
	        if ($rs && $rs->rowCount() == 1) {
	            $user = $rs->fetch(PDO::FETCH_ASSOC);
	            ob_start();
		        require 'Templates/password_change.php';
		        echo ob_get_clean();
	        } else {
				echo 'Token is invalid !';
			}
	    }
		exit;
	}

	public function update() {
		if (empty($_GET['token']) || empty($_POST['token']) || empty($_POST['id']) || empty($_POST['password'])) {
			$this->result['error'] = 'Invalid request paramters';
			return;
		}
		$qry = "SELECT `u`.`id` FROM  `user` AS  `u` WHERE `u`.`id` = '" . $_POST['id'] . "' AND `u`.`recover_token` = '" . $_POST['token'] . "' LIMIT 0 , 1";
		$rs = DB::query($qry);
        if ($rs && $rs->rowCount() == 1) {
        	$qry = "UPDATE user SET password='" . $_POST['password'] . "', recover_token=NULL, recover_expire=NULL WHERE id=" . $_POST['id'];
        	if (!DB::exec($qry)) 
        		$this->result['error'] = 'Error on update password';
        } else {
        	$this->result['error'] = 'No user found for paramters';
        }
	}

}