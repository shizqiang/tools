var Player = function(id, name) {
	this.id = id;
	this.name = name;
	this.is_killed_tonight = false; // 是否今晚被狼杀
	this.is_dead = false; // 是否死亡
	this.is_police = false; // 是否是警长
	this.identity = ''; // 身份
	this.can_check_identity = false; // 预言家是否可查看别人身份 
	this.votes = []; // 被袭击的狼人列表
	this.doubt_id = ''; // 票谁
	this.can_doubt = false; // 是否可以投票
	this.tickets = []; // 被投票的玩家列表 
	this.can_attack = false; // 狼人是否可以袭击玩家
	this.attack_id = ''; // 狼人袭击的人

	this.sleep = false; // 玩家是否睡着

	this.has_antidote = false; // 有灵药
	this.has_poison = false; // 有毒药
	this.can_use_antidote = false; // 可以使用灵药
	this.can_use_poison = false; // 可以使用毒药

	this.can_shot = false; // 猎人可以开枪

	this.last_words = false; // 是否可以有遗言
	
};

Player.prototype = {
	test2: function() {
		console.log('test2');
	}	
};




var Night = function(sockets, players) {
	this.sockets = sockets;
	this.players = players;
	sockets.map(s => {
		s.emit('night');
	});
};

Night.prototype = {

	// 救活被袭击的人
	save: function() {
		this.players.map(p => {
			if (p.is_killed_tonight) {
				p.is_killed_tonight = false;
			}
		});
	},

	// 女巫下毒
	kill: function(id) {
		this.sockets.map(s => {
			if (s.player.id === id) {
				if (s.is_dead) {
					throw new Error('不能毒杀死人');
				}
				s.emit('message', '你昨晚死亡'); 
				s.player.is_dead = true;
			}
		});
	}
};

module.exports = {
	Player: Player,
	Night: Night
};