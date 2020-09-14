console.log(__dirname)
let user = '051011477981'
let pass = '32090713'
	
	
var fs = require('fs');

// npm i ini --registry=https://registry.npm.taobao.org
var ini = require('ini');
var config = ini.parse(fs.readFileSync('.env', 'utf-8'));


process.env.NODE_ENV = config.app.env;
process.env.PORT = config.app.port;

// npm i express --registry=https://registry.npm.taobao.org
var app = express();
global.config = config