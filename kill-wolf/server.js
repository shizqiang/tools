var express = require('express');
const app = express();
var http = require('http').createServer(app);
var io = require('socket.io')(http);
var path = require('path');
var modle = require('./model');

app.use(express.static(path.join(__dirname, 'public')));

app.get('/', (req, res) => {
	res.sendFile(__dirname + '/index.html');
});

let roles = ['wolf', 'wolf', 'prophet', 'witch', 'hunter', 'man', 'man', 'man'];
roles = ['wolf', 'wolf', 'witch', 'prophet'];
var sockets = [], players = [], night = null, started = false;

function check() {
	let human = 0;
	let wolf = 0;
	sockets.map(s => {
		if (s.player.identity === 'wolf' && !s.player.is_dead) {
			wolf++;
		}
		if (s.player.identity !== 'wolf' && !s.player.is_dead) {
			human++;
		}
		s.emit('light');
		s.emit('players', players, s.player);
	});
	
	if (wolf === 0) {
		// 好人获胜
		sockets.map(s => {
			s.player.is_dead = false;
			s.player.can_use_antidote = false;
			s.player.can_use_poison = false;
			s.player.sleep = false;
			s.player.can_doubt = false;
			s.emit('human-win');
		});
		started = false;
		console.log('游戏结束，好人胜利');
	}
	if (human === 0 || (human === 1 && wolf === 1)) {
		// 狼人获胜
		sockets.map(s => {
			s.player.is_dead = false;
			s.player.can_use_antidote = false;
			s.player.can_use_poison = false;
			s.player.sleep = false;
			s.player.can_doubt = false;
			s.emit('wolf-win');
		});
		started = false;
		console.log('游戏结束，狼人胜利');
	}
	sockets.map(s => {
		s.emit('players', players, s.player);
	});
}

function light() {
	sockets.map(s => {
		s.player.votes = [];
		s.player.tickets = [];
		s.player.attack_id = '';
		if (s.player.is_killed_tonight) {
			s.player.is_dead = true;
		}
		if (!s.player.is_dead) {
			s.player.can_doubt = true;
		}
		s.player.is_killed_tonight = false;
	});
	check();
}

io.on('connection', (socket) => {
	
	socket.on('heart', _ => {
	});

	// 玩家取名加入游戏
	socket.on('name', name => {
		let player = new modle.Player(socket.client.id, name);
		socket.player = player;
		if (started) {
			socket.emit('message', '游戏进行中，不能加入');
			return;
		}
		if (sockets.length === roles.length) {
			socket.emit('message', '人数已满');
			return;
		}
		console.log(player.name + ' -> 加入了游戏');

		// 将自己加入玩家
		sockets.push(socket);
		players.push(player);
		sockets.map(s => {
			s.emit('players', players, s.player);
		});
	});
	
	// 玩家退出游戏
	socket.on('disconnect', _ => {
		if (!socket.player) {
			return;
		}
		console.log(socket.player.name + ' -> 退出了游戏');
		sockets = sockets.filter(s => {
			return s.player.id !== socket.player.id;
		})
		players = players.filter(p => {
			return p.id !== socket.player.id;
		});
		sockets.map(s => {
			s.emit('players', players, s.player);
		});
		if (started) {
			check();
		}
	});
	
	// 玩家进入睡觉
	socket.on('sleep', _ => {
		if (!socket.player) {
			return;
		}
		socket.player.sleep = true;
		let num = 0;
		sockets.map(s => {
			s.emit('players', players, s.player);
			if (s.player.sleep) {
				num++;
			}
		});
		console.log(socket.player.name + ' -> 进入睡眠');
		console.log('当前睡眠人数' + num, roles.length);
		if (!started && num < roles.length) {
			console.log('人数不足，无法进入黑夜');
			return;
		}
		num = 0;
		sockets.map(s => {
			if (!s.player.sleep && !s.player.is_dead) {
				num++;
			}
		});
		if (!started) {
			started = true; // 游戏开始
			roles.sort(function(a, b) {
				return Math.random() - 0.5;
			});
			// 初始化所有的角色
			let _roles = [];
			players.map(p => {
				p.identity = roles.pop();
				_roles.push(p.identity);
				if (p.identity === 'prophet') {
					p.can_check_identity = true;
				}
				if (p.identity === 'witch') {
					p.has_antidote = true;
					p.has_poison = true;
				}
				if (p.identity === 'wolf') {
					p.can_attack = true;
				}
				if (p.identity === 'hunter') {
					p.can_shot = true;
				}
			});
			roles = _roles;
			sockets.map(s => {
				s.emit('players', players, s.player);
			});
			// 新的夜晚
			console.log('游戏开始，进入黑夜');
		} else {
			if (num > 0) {
				console.log('还有人没睡觉');
				return;
			}
			players.map(p => {
				
				if (p.identity === 'prophet') {
					p.can_check_identity = !p.is_dead;
				}
				if (p.identity === 'wolf') {
					p.can_attack =  !p.is_dead;
				}
				if (p.identity === 'hunter') {
					p.can_shot =  !p.is_dead;
				}
			});
			sockets.map(s => {
				s.emit('players', players, s.player);
			});
			console.log('进入黑夜');
		}
		
		night = new modle.Night(sockets, players);
		sockets.map(s => {
			// 唤醒预言家
			if (s.player.identity === 'prophet') {
				s.emit('prophet');
				s.emit('players', players, s.player);
			}
			// 唤醒狼人
			if (s.player.identity === 'wolf') {
				s.emit('wolf');
				s.emit('players', players, s.player);
			}
		});	
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
		let attacked = '', lastAttacked = '';
		sockets.map(s => {
			// 去掉已经选择的袭击对象
			if (s.player.id == socket.player.attack_id) {
				lastAttacked = socket.player.attack_id;
				s.player.votes = s.player.votes.filter(item => {
					return item.id != socket.player.id
				});
			}
		});
		sockets.map(s => {
			if (s.player.id == id) {
				if (s.player.is_dead) {
					socket.emit('message', '他已经死了');	
					socket.player.attack_id = lastAttacked;
				} else {
					s.player.votes.push({id: socket.player.id, name: socket.player.name});
					socket.player.attack_id = id;
				}
				
			}
		});

		sockets.map(s => {
			if (s.player.identity === 'wolf') {
				s.emit('players', players, false);
			}
		});
		
		// 狼人是否都袭击结束
		let ready = true;
		sockets.map(s => {
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
			return;
		}
		sockets.map(s => {
			if (s.player.identity === 'wolf') {
				s.player.can_attack = false;
				s.emit('players', false, s.player);
			}
		});

		// 夜晚死亡的玩家
		players.map(player => {
			if (player.id === attacked) {
				player.is_killed_tonight = true;
			}
		});

		sockets.map(s => {
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
			night.save();
		}
		if (!socket.player.has_poison) {
			socket.player.can_use_poison = false;
		}
		socket.emit('players', false, socket.player);
		if (!socket.player.can_use_poison) {
			// 天亮
			light();
		}
	});

	// 女巫使用毒药
	socket.on('poison', id => {
		try {
			if (id) {
				night.kill(id);
				socket.player.has_poison = false;
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
		let ready = true;
		sockets.map(s => {
			if (!s.player.is_dead) {
				if (s.player.doubt_id === '') {
					// 有人未投票
					console.log('还有人未投票')
					ready = false;
				}
			}
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
				max = s;
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
			s.player.can_doubt = false;
			s.player.sleep = false;
			s.player.tickets = [];
		});
		sockets.map(s => {
			s.emit('players', players, s.player);
		});
		check();
	});
});

// socket.broadcast.emit('sleep'); // 不包括自己
http.listen(3000, () => {
	console.log('listening on *:3000');
});