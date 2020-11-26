<!DOCTYPE html>
<html>
	<head>
    <?php include 'header.php';?>
    </head>
    <body>
    <img alt="qrcode" src="<?=$_REQUEST['qrcode']?>">
    	<input id="oneCode" type="text"><button onclick="active();">激活</button>
    </body>
    <script type="text/javascript">
	function active() {
		var code = document.getElementById('oneCode').value;
		fetch("/register.php?f=active&token=<?=_GET_('token', '')?>", {
		    body: "code=" + code,
		    headers: {
		      'content-type': 'application/x-www-form-urlencoded;charset=UTF-8',
		      'X-Requested-With': 'xmlhttprequest'
		    },
		    method: 'POST', // *GET, POST, PUT, DELETE, etc.
		}).then(response => console.log(response.json()))
	}
    </script>
</html>