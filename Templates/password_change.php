<html>
<head>
<title>Changement de votre mot de passe</title>
<script type="text/javascript">
	function verify(form) {
		if (form.password.value.length < 6) {
			alert("Mot de passe trop cours.\n6 caractères minimum.");
		} else if (form.password.value != form.confirm.value) {
			alert("Mot de passe différents.");
		} else {
			var xhr = new XMLHttpRequest();
			xhr.open('POST', '<?=BASE_URL?>api/v1/password/update?token=<?=$user['recover_token']?>', true);
			xhr.onload = function(e) {
				if (this.status == 200) {
					var result = JSON.parse(this.responseText);
					if (!result.success) alert(result.error);
					else {
						alert("Votre mot de passe à bien été changé");
						document.location = '<?=BASE_URL?>api/v1/password/change?finish=1';
					}
				}
			};
			var data = 'id=<?=$user['id']?>&token=<?=$user['recover_token']?>&password=' + form.password.value;
			xhr.send(data);
		}
		return false;
	}
</script>
</head>
<body>
	Bonjour <?=$user['name']?><br/>
	<small>Nouveau mot de passe (6 caractères minimum)</small>
	<form onsubmit="return verify(this);">
		<input type="password" name="password" value="benpass" /><br/>
		<input type="password" name="confirm" value="benpass" /><br/>
		<input type="submit" value="Sauvegarder">
	</form>
</body>
</html>