var fs = require('fs');
var os = require('os');
const { exec } = require('child_process');

var worker = 1;

function test() {
	console.log('start test')
    exec('php queue.php', (err, stdout) => {
		if (err) {
            console.error(err)
        }
		console.log(stdout)
	});
}

for (let i = 0; i < worker; i++) {
    test()
}

