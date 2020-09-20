var Player = function(id, name) {
	this.id = id;
	this.name = name;
	this.dead = false; // 是否死亡
	this.identity = ''; // 身份
	this.votes = []; // 被投票的人
	this.doubt = null; // 票谁
	this.attack = null; // 狼人袭击谁
	this.sleep = false; // 玩家进入睡眠
	this.antidote = true; // 解药
	this.poison = true; // 毒药
	this.shot = true; // 猎人开枪
	this.lastWords = false; // 是否可以有遗言
};

var Day = function() {

};

var Night = function(sockets) {
	this.sockets = sockets;
	this.prophet = null; // 预言家查验的人
	this.attacked = null; // 被袭击的人
	this.antidote = false; // 是否使用解药
	this.poison = false; // 是否使用了毒药
};

Night.prototype = {

	// 唤醒预言家和狼人
	wakeUpProphetAndWolf: function() {
		let wolfs = [], players = [];
		for (let socket of Object.values(this.sockets)) {
			socket.emit('sleep');
			let player = socket.player;
			players.push(player);
			// 唤醒预言家
			if (player.identity === 'prophet' && !player.dead) {
				socket.emit('prophet');
			}
			// 唤醒狼人
			if (player.identity === 'wolf' && !player.dead) {
				wolfs.push(socket);
			}
		}
		wolfs.map(socket => {
			socket.emit('wolf', players);
		})
		
	},

	// 救活被袭击的人
	save: function() {
		let players = [];
		for (let socket of Object.values(this.sockets)) {
			let player = socket.player;
			// 唤醒预言家
			if (player.id === this.attacked && player.dead) {
				player.dead = false;
			}
		}
	},

	// 女巫下毒
	kill: function(id) {
		for (let socket of Object.values(this.sockets)) {
			let player = socket.player;
			// 唤醒预言家
			if (player.id === id) {
				if (player.dead) {
					throw new Error('不能毒杀死人');
				}
				player.dead = true;
			}
		}
	}
};

module.exports = {
	Player: Player,
	Day: Day,
	Night: Night
};