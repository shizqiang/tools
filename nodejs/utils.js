var axios = require('axios');

async function show() {
	var response = await axios.get('https://dev.lsa0.cn/upload.php');
	console.log(response.data);
}

show();
