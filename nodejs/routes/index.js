var express = require('express');
var router = express.Router();
var utils = require('../utils');

/* GET home page. */
router.get('/', function(req, res, next) {
  res.render('index', { title: 'Express' });
});

router.get('/test', async function(req, res, next) {
	utils.query('select * from users where id = ?', [1]).then(users => {
		res.json(users);
	}).catch(e => {
		res.end(e.message);
	});
});

module.exports = router;
