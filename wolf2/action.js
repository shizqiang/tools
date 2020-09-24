function _() {
	console.log(this.name + '什么也干不了');
}

function sleep(socket) {
	this.sleep = true;
	this.action = _;
	console.log(socket.player.name + ' -> 进入睡眠');
	
}

function kill() {
	console.log('kill')
}

function check_identity() {
	console.log('check_identity');
}

module.exports = {
		_: _,
		sleep: sleep,
		kill: kill,
		check_identity: check_identity
};