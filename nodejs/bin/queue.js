var fs = require('fs');
var os = require('os');
const { exec } = require('child_process');

// 旧的环境指定方式
var argv = process.argv.splice(2), env = {"d": "development", "t": "testing", "p": "production"}, worker = 1;

// 新的环境指定方式
var fs = require('fs');
if (!fs.existsSync('.env')) {
	console.error('No env file');
	process.exit();
}
var ini = require('ini');
var config = ini.parse(fs.readFileSync('.env', 'utf-8'));
global.config = config
if (argv.length > 0) {
	env = env[argv[0]];
	if (!env) {
        env = 'development';
	}
} else {
    env = 'development';
}
if (env == 'production') {
    worker = 2;
}

const logFile = 'queue-server.';
let execPath = 'CI_ENV=' + env + ' php /var/www/lsa0.cn/cli/index.php queue ';
if (env == 'testing') {
    execPath = 'php /var/www/test.lsa0.cn/cli/index.php queue ';
}

function user_trans() {
    exec(execPath + 'user_trans', (err, stdout) => {
		if (err) {
            console.error(err)
        }
        if (stdout) {
		    log4js(stdout)
        }
		user_trans();
	});
}

function hr_user_withdraw_main() {
    exec(execPath + 'hr_user_withdraw_main', (err, stdout) => {
		if (err) {
            console.error(err)
        }
        if (stdout) {
		    log4js(stdout)
        }
		hr_user_withdraw_main();
	});
}

function template_message() {
    exec(execPath + 'template_message', (err, stdout) => {
		if (err) {
            console.error(err)
        }
        if (stdout) {
		    log4js(stdout)
        }
		template_message();
	});
}

function update_user() {
    exec(execPath + 'update_user', (err, stdout) => {
		if (err) {
            console.error(err)
        }
        if (stdout) {
		    log4js(stdout)
        }
		update_user();
	});
}

function tag_user() {
    exec(execPath + 'tag_user', (err, stdout) => {
        if (err) {
            console.error(err)
        }
        if (stdout) {
            log4js(stdout)
        }
        tag_user();
    });
}

function log4js(log) {
    var date = new Date();
    var fileName = logFile + date.toLocaleDateString().replace(/\//g, "-") + ".log";
    fs.appendFile('/tmp/' + fileName, date.toLocaleString() + ' -> ' + JSON.stringify(log) + os.EOL, function(err) {
        if (err) {
            console.error(err)
        }
    });
}

for (let i = 0; i < worker; i++) {
    user_trans()
    hr_user_withdraw_main()
    template_message()
    update_user()
    tag_user()
}

