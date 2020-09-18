var express = require('express');
var router = express.Router();

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', { title: 'Express' });
});

router.get('/test', async function(req, res, next) {
	req.session.name = 'xxx';
	var mysql = require('mysql');
	var connection = mysql.createConnection(config['mysql']);
	connection.connect();
	connection.query('SELECT * from opp_users', function (error, results, fields) {
	  if (error) throw error;
	  res.json(results)
	});

	connection.end();
	
});

module.exports = router;
