<html>
<head>
<title><?=$user['subject']?></title>
</head>
<body>
	Bonjour <?=$user['name']?><br/>
	Veuillez suivre le <a href='<?=$user['link']?>'>lien</a> suivant afin de ré-initialiser votre mot de passe.<br/>
	<small>Ce lien ne sera valable que pendant 1 heure</small>
</body>
</html>