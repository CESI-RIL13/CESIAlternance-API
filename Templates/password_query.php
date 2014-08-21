<html>
<head>
<title>Demande de changement de mot de passe</title>
<script type="text/javascript">
	function verify(form) {
		var xhr = new XMLHttpRequest();
		xhr.open('POST', '<?=BASE_URL?>api/v1/password/request', true);
		xhr.onload = function(e) {
			if (this.status == 200) {
				var result = JSON.parse(this.responseText);
				if (!result.success) alert(result.error);
				else alert("Un lien de modification de mot de passe a été envoyé sur votre adresse email '" + form.email.value + "'");
			}
		};
		var data = 'email=' + form.email.value;
		xhr.send(data);
		return false;
	}
</script>
</head>
<body>
	Bonjour<br/>
	<small>Veuillez saisir votre adresse email.</small>
	<form onsubmit="return verify(this);">
		<input type="email" name="email" value="benjamin@kolapsis.com" /><br/>
		<input type="submit" value="Envoyer">
	</form>
</body>
</html>