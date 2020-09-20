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

var sockets = {}, players = [], night = null;

function __players() {
	let players = [];
	for (let socket of Object.values(sockets)) {
		players.push(socket.player);
	}
	return players;
}

io.on('connection', (socket) => {
	
	// 玩家退出游戏
	socket.on('disconnect', _ => {
		if (!socket.player) {
			return;
		}
		console.log(socket.player.name + ' -> 退出了游戏');
		delete sockets[socket.player.id];
		for (let id in sockets) {
			if (id === socket.player.id) {
				continue;
			}
			sockets[id].emit('leave', socket.player.id)
		}
	});
	
	socket.on('message', (msg) => {
		for (let key in sockets) {
			if (key === socket.client.id) {
				continue;
			}
			sockets[key].send(msg)
		}
	});
	
	// 玩家取名加入游戏
	socket.on('name', name => {
		if (!name) {
			socket.emit('rename');
			return;
		}
		let player = new modle.Player(socket.client.id, name);
		socket.player = player;
		console.log(player.name + ' -> 加入了游戏');
		// 初始化已加入的玩家
		let players = [];
		for (let id in sockets) {
			players.push(sockets[id].player);
		}
		socket.emit('players', players);

		// 将自己加入玩家
		sockets[player.id] = socket;
		for (let id in sockets) {
			sockets[id].emit('join', socket.player);
		}
		
	});
	
	// 玩家进入睡觉
	socket.on('sleep', _ => {
		if (socket.player.sleep) {
			return;
		}
		socket.player.sleep = true;
		socket.emit('sleeped');
		console.log(socket.player.name + ' -> 进入睡眠');
		let num = 0;
		for (let id in sockets) {
			if (!sockets[id].player.sleep) {
				// 还有人没睡觉，不进入黑夜
				return;
			}
			num++;
		}
		let roles = ['wolf', 'wolf', 'prophet', 'witch', 'hunter', 'man', 'man', 'man'];
		roles = ['wolf', 'witch', 'wolf'];
		if (num < roles.length) {
			console.log('人数不足，无法进入黑夜');
			return;
		}
		roles.sort(function(a, b) {
             return Math.random() - 0.5;
        });
        // 初始化所有的角色
		for (let id in sockets) {
			let player = sockets[id].player;
			player.identity = roles.pop();
		}

		// 新的夜晚
		console.log('游戏开始，进入黑夜');
		night = new modle.Night(sockets);
		night.wakeUpProphetAndWolf();
	});
	
	// 预言家查看身份
	socket.on('identity', _id => {
		if (socket.player.identity !== 'prophet' || socket.player.dead) {
			return;
		}
		for (let id in sockets) {
			if (sockets[id].player.id === _id) {
				if (night.prophet === _id || night.prophet === null) {
					night.prophet = _id;
					if (sockets[id].player.identity === 'wolf') {
						socket.emit('identity', '狼人');
					} else {
						socket.emit('identity', '好人');
					}
				} else {
					socket.emit('identity', '只能检查一个人');
				}
			} 
		}
	});

	// 狼人袭击
	socket.on('attack', id => {
		if (socket.player.identity !== 'wolf' || socket.player.dead || night.attacked) {
			return;
		}
		let players = [], wolfs = [], witch = null, hunter = null, attacked = '';
		for (let s of Object.values(sockets)) {
			// 去掉已经选择的袭击对象
			if (s.player.id == socket.player.attack) {
				s.player.votes = s.player.votes.filter(item => {
					return item.id != socket.player.id
				});
			}
			if (s.player.id == id) {
				s.player.votes.push({id: socket.player.id, name: socket.player.name});
			}
			players.push(s.player);
			if (s.player.identity === 'wolf') {
				wolfs.push(s);
			}
			if (s.player.identity === 'witch' && !s.player.dead) {
				witch = s;
			}
			if (s.player.identity === 'hunter' && !s.player.dead) {
				hunter = s;
			}
		}
		
		socket.player.attack = id;
		// 通知所有的狼人袭击对象
		wolfs.map(wolf => {
			wolf.emit('attack', players);
		});
		
		// 狼人是否都袭击结束
		let ready = true, _attack = '';
		wolfs.map(wolf => {
			if (wolf.player.attack === null) {
				// 有狼人未选择
				ready = false;
			} else if (wolf.player.attack != '-') { // 狼人不杀人
				if (_attack === '') {
					_attack = wolf.player.attack;
				} else {
					if (wolf.player.attack !== _attack) {
						ready = false;
					}
				}
			}
		});
		if (!ready) {
			return;
		}
		attacked = _attack;

		// 夜晚死亡的玩家
		players.map(player => {
			if (player.id === attacked) {
				player.dead = true;
				night.attacked = attacked;
			}
		});

		if (witch) {
			witch.player.antidote_ = false;
			witch.emit('witch');
			witch.emit('players', players, witch.player);
		} else if (hunter) {
			hunter.emit('hunter');
		}
	});

	// 女巫使用灵药
	socket.on('antidote', Yes => {
		if (socket.player.dead && night.attacked !== socket.player.id) {
			return;
		}
		if (socket.player.identity !== 'witch' || !socket.player.antidote) {
			return;
		}
		if (night.antidote) {
			return;
		}
		night.antidote = true; // 已使用灵药
		if (Yes === 1) {
			socket.player.antidote = false; // 灵药标记为已使用
			// 救活被袭击的人
			night.save();
		}
		socket.player.antidote_ = true;
		socket.emit('players', __players(), socket.player);
	});

	// 女巫使用毒药
	socket.on('poison', id => {
		if (socket.player.dead && night.attacked !== socket.player.id) {
			return;
		}
		if (socket.player.identity !== 'witch' || !socket.player.poison) {
			return;
		}
		if (!night.antidote) { // 还没有使用灵药
			return;
		}
		if (night.poison) { // 已经使用过毒药
			return;
		}
		try {
			night.kill(id);
			socket.player.poison = false; // 毒药标记为已使用
			socket.emit('players', __players(), socket.player);
		} catch (e) {
			socket.emit('message', e.message);
		}
		
	});
});

// socket.broadcast.emit('sleep'); // 不包括自己
http.listen(3000, () => {
	console.log('listening on *:3000');
});