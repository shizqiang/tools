var express = require('express');
const app = express();
var http = require('http').createServer(app);
var io = require('socket.io')(http);
var path = require('path');
const Player = require('./player'); 

app.use(express.static(path.join(__dirname, './public')));

var sockets = [];
var players = [];
var dead = []; // 定义昨夜死亡的玩家
var day = 0;
var started = false;
var identities = ['wolf', 'prophet', 'witch', 'hunter', 'man'];

identities = ['wolf', 'prophet', 'witch'];

// 返回一个随机的身份分配
function random() {
	identities.sort(function(a, b) {
		return Math.random() - 0.5;
	});
	return identities.slice();
}

// 游戏结束
function game_over(winner) {
	sockets.forEach(s => {
		let id = s.player.id;
		let name = s.player.name;
		s.player = new Player(id, name);
		s.emit(winner);
	});
	started = false;
	day = 0;
}

// 检查游戏是否结束
function check_game_over() {
	// 剩余的身份人数
	let human = 0, wolf = 0, god = 0;
	sockets.forEach(s => {
		if (s.player.identity === 'wolf' && !s.player.is_dead) {
			wolf++;
		}
		if (s.player.identity === 'man' && !s.player.is_dead) {
			human++;
		}
		if (s.player.identity !== 'man' && s.player.identity !== 'wolf' && !s.player.is_dead) {
			god++;
		}
		
	});
	
	if (human === 0 || god === 0 || (human === 1 && wolf === 1)) {
		// 狼人获胜
		game_over('游戏结束，狼人胜利');
	} else if (wolf === 0) {
		// 好人获胜
		game_over('游戏结束，好人胜利');
	}
}


// 天亮了
function light() {
	sockets.forEach(s => {
		s.player.votes = [];
		s.player.tickets = [];
		s.player.attack_id = '';
		if (s.player.is_killed_tonight) {
			if (day === 1) {
				s.emit('message', '你昨晚死亡，请留遗言'); 
			} else {
				s.emit('message', '你昨晚死亡，没有遗言'); 
			}
			s.player.is_dead = true;
			s.player.is_killed_tonight = false;
			dead.push(s.player.name);
		} 
		if (s.player.is_poison_tonight) {
			// 被女巫毒死
			if (day === 1) {
				s.emit('message', '你昨晚死亡，请留遗言'); 
			} else {
				s.emit('message', '你昨晚死亡，没有遗言'); 
			}
			if (s.player.identity === 'hunter') {
				// 被女巫毒死，不能开枪
				s.player.can_shot = false;
			}
			s.player.is_dead = true;
			s.player.is_poison_tonight = false;
			dead.push(s.player.name);
		}
	});
	sockets.forEach(s => {
		if (!s.player.is_dead) {
			if (dead.length > 0) {
				s.emit('message', dead.join() + ' -> 昨晚死亡'); 
			} else {
				s.emit('message', '昨晚是平安夜'); 
			}
			s.player.can_doubt = true;
		}
	});
	sockets.forEach(s => {
		s.emit('light');
		s.emit('players', players, s.player);
	})
	check_game_over();
}

// 天黑了
function night() {
	day++;
	console.log('现在是第' + day + '个夜晚');
	if (started) {
		players.forEach(p => {
			if (p.identity === 'wolf') {
				p.can_attack = true;
			}
			if (p.identity === 'prophet') {
				p.can_check_identity = true;
			}
		});
	} else {
		started = true;
		console.log('游戏开始');
		let identities = random();
		players.forEach(p => {
			p.identity = identities.pop();
			if (p.identity === 'wolf') {
				p.can_attack = true;
			}
			if (p.identity === 'prophet') {
				p.can_check_identity = true;
			}
			if (p.identity === 'witch') {
				// 女巫初始化毒药和灵药
				p.has_antidote = true;
				p.has_poison = true;
			}
			if (p.identity === 'hunter') {
				// 猎人可以开枪
				p.can_shot = true;
			}
		});
	}
	console.log('进入黑夜');
}


io.on('connection', socket => {

	// 玩家取名加入游戏
	socket.on('name', (id, name) => {
		let player = new Player(id, name);
		socket.player = player;
		if (started) {
			// 查询id是否是掉线的用户
			let offline = false;
			sockets.forEach(s => {
				if (s.player.offline && s.player.id === id) {
					// 将s替换为当前socket，并将offline标注为false
					s.player.offline = false;
					socket.player = s.player;
					s = socket;
					console.log(player.name + ' -> 重新连接到了游戏');
					socket.broadcast.emit('players', players, s.player);
					socket.emit('players', players, s.player);
					offline = true;
				}
			});
			if (!offline) {
				socket.emit('message', '游戏进行中，不能加入');
			}
		} else {
			if (sockets.length >= 12) {
				socket.emit('message', '房间人数已满');
			} else {
				console.log(player.name + ' -> 加入了游戏');
				// 将自己加入玩家
				sockets.push(socket);
				players.push(player);
				socket.broadcast.emit('players', players, player);
				socket.emit('players', players, player);
			}
		}
	});
	
	// 玩家退出游戏
	socket.on('disconnect', _ => {
		if (!socket.player) {
			return false;
		}
		if (started) {
			console.log(socket.player.name + ' -> 掉线了');
			sockets.forEach(s => {
				if (s.player.id === socket.player.id) {
					// 游戏结束后，这个用户需要清除掉
					s.player.offline = true;
				}
			});
		} else {
			console.log(socket.player.name + ' -> 退出了游戏');
			sockets = sockets.filter(s => {
				return s.player.id !== socket.player.id;
			});
			players = players.filter(p => {
				return p.id !== socket.player.id;
			});
		}
		// 通知所有玩家
		socket.broadcast.emit('players', players, null);
		if (started) {
			// 检查游戏是否结束
			console.log('玩家退出游戏，检查游戏是否结束');
		}
	});
	
	socket.on('sleep', _ => {
		socket.player.sleep = true;
		socket.broadcast.emit('players', players, null);
		socket.emit('players', players, socket.player);
		// 判断所有玩家是否都准备好
		let all = players.every(p => {
			return p.sleep || p.is_dead;
		});
		if (all) {
			// 进入黑夜
			night();
			sockets.forEach(s => {
				s.emit('night');
				// 唤醒预言家
				if (s.player.identity === 'prophet') {
					s.emit('prophet');
				}
				// 唤醒狼人
				if (s.player.identity === 'wolf') {
					s.emit('wolf');
					s.emit('players', players, s.player);
				}
			});	
		}
	});
	
	// 预言家查看身份
	socket.on('identity', id => {
		players.map(p => {
			if (p.id === id) {
				if (p.identity === 'wolf') {
					socket.emit('message', p.name + ' -> 狼人');
				} else {
					socket.emit('message', p.name + ' -> 好人');
				}
				socket.player.can_check_identity = false;
				socket.emit('players', false, socket.player);	
			} 
		});
	});

	// 狼人袭击
	socket.on('attack', id => {
		if (!socket.player.can_attack) {
			console.log('袭击已结束，不能再选择袭击对象');
			return false;
		}
		let attacked = '', lastAttacked = '';
		sockets.forEach(s => {
			// 去掉已经选择的袭击对象
			if (s.player.id == socket.player.attack_id) {
				lastAttacked = socket.player.attack_id;
				s.player.votes = s.player.votes.filter(item => {
					return item.id != socket.player.id
				});
			}
		});
		sockets.forEach(s => {
			if (s.player.id == id) {
				if (s.player.is_dead) {
					socket.emit('message', '他已经死了，不能再袭击');	
					socket.player.attack_id = lastAttacked;
				} else {
					s.player.votes.push({id: socket.player.id, name: socket.player.name});
					socket.player.attack_id = id;
				}
				
			}
		});

		// 通知其他狼人袭击结果
		sockets.forEach(s => {
			if (s.player.identity === 'wolf') {
				s.emit('players', players, s.player);
			}
		});
		
		// 狼人是否都袭击结束
		let ready = true;
		sockets.forEach(s => {
			if (s.player.identity === 'wolf' && !s.player.is_dead) {
				if (s.player.attack_id === '') {
					// 有狼人未选择
					ready = false;
				} else { // 狼人不杀人
					if (attacked === '') {
						attacked = s.player.attack_id;
					} else {
						if (s.player.attack_id !== attacked) {
							// 选择不一致
							ready = false;
						}
					}
				}
			}
		});
		if (!ready) {
			return false;
		}
		players.forEach(p => {
			if (p.identity === 'wolf') {
				p.can_attack = false;
			}
		});

		// 夜晚死亡的玩家
		players.forEach(player => {
			if (player.id === attacked) {
				player.is_killed_tonight = true;
			} else {
				player.is_killed_tonight = false;
			}
		});

		// 狼人袭击结束，唤醒女巫
		sockets.forEach(s => {
			if (s.player.identity === 'witch') {
				s.player.can_use_antidote = true;
				s.player.can_use_poison = false;
				if (!s.player.has_antidote) {
					s.player.can_use_antidote = false;
					s.player.can_use_poison = true;
				}
				if (!s.player.has_poison) {
					s.player.can_use_poison = false;
				}
				s.emit('witch');
				s.emit('players', players, s.player);
			}
		});
	});

	// 女巫使用灵药
	socket.on('antidote', Yes => {
		socket.player.can_use_antidote = false;
		socket.player.can_use_poison = true;
		if (Yes === 1) {
			socket.player.can_use_poison = false;
			socket.player.has_antidote = false; // 灵药标记为已使用
			// 救活被袭击的人
			players.forEach(p => {
				if (p.is_killed_tonight) {
					p.is_killed_tonight = false;
				}
			});
		}
		if (!socket.player.has_poison) {
			socket.player.can_use_poison = false;
		}
		socket.emit('players', players, socket.player);
		if (!socket.player.can_use_poison) {
			// 天亮了
			light();
		}
	});

	// 女巫使用毒药
	socket.on('poison', id => {
		try {
			if (id) {
				night.kill(id);
				socket.player.has_poison = false;
				poison = id;
			}
			socket.player.can_use_poison = false;
			light();
		} catch (e) {
			socket.emit('message', e.message);
		}
	});

	socket.on('witch_is_dead', _ => {
		light();
	});

	socket.on('ticket', id => {
		let lastDoubtId = '';
		sockets.map(s => {
			// 去掉已经选择的袭击对象
			if (s.player.id == socket.player.doubt_id) {
				lastDoubtId = socket.player.doubt_id;
				s.player.tickets = s.player.tickets.filter(item => {
					return item.id != socket.player.id
				});
			}
		});
		sockets.map(s => {
			if (s.player.id == id) {
				if (s.player.is_dead) {
					socket.emit('message', '他已经死了');	
					socket.player.doubt_id = lastDoubtId;
				} else {
					s.player.tickets.push({id: socket.player.id, name: socket.player.name});
					socket.player.doubt_id = id;
				}
			}
		});

		// 是否投票结束
		let ready = players.every(p => {
			return p.is_dead || p.doubt_id !== '';
		});
		sockets.map(s => {
			s.emit('players', players, false);
		});
		if (!ready) {
			return;
		}
		let max = null;
		sockets.map(s => {
			if (s.player.is_dead) {
				return;
			}
			if (max == null) {
				if (s.player.tickets.length > 0) {
					max = s;
				}
			} else if (s.player.tickets.length > max.player.tickets.length) {
				max = s;
			} else if (s.player.tickets.length == max.player.tickets.length) {
				ready = false;
			}
		});
		// 平票
		if (!ready) {
			console.log('平票');
			return;
		}
		max.player.is_dead = true;
		sockets.map(s => {
			if (s.player.id === max.player.id) {
				s.emit('message', '你被投票处决，请留遗言');
			} else {
				s.emit('message', max.player.name + ' -> 被投票处决，他有遗言'); 
			}
			s.player.can_doubt = false;
			s.player.sleep = false;
			s.player.tickets = [];
			s.player.doubt_id = '';
		});
		broadCast();
		check();
	});
});

// socket.broadcast.emit('sleep'); // 不包括自己
http.listen(3000, () => {
	console.log('listening on *:3000');
});