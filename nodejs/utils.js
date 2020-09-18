var axios = require('axios');
var mysql = require('mysql');

async function show() {
	var response = await axios.get('https://120.27.24.27/upload.php');
	console.log(response.data);
}

//show();

function connect() {
	var connection = mysql.createConnection(config['mysql']);
	connection.connect();
	return connection;
}

function query(sql, bind = []) {
	return new Promise(function(resolve, reject) {
		const conn = connect();
		conn.query(sql, bind, function (error, results, fields) {
		  if (error) {
			  reject(error);
		  } else {
			  resolve(results);
		  }
		});
		conn.end();
	})
}

module.exports = {
	query: query	
};