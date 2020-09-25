var app = new Vue({
    el: '#app',
    data: {
        name: 'kill wolf',
        night: false,
        players: [],
        player: {
        	sleep: true, 
        	offline: false, 
        	name: ''
        },
        message: '',
        disconnected: false
    },
    created() {
    	let id = localStorage.getItem('id');
		if (!id) {
			localStorage.setItem('id', Date.now());
		}
    	let name = localStorage.getItem('name');
		if (name) {
            this.player.name = name;
		}
    },
    methods: {
    	
    	setName: function() {
            if (this.player.name) {
                localStorage.setItem('name', this.player.name);
    		    socket.emit('name', localStorage.getItem('id'), this.player.name);
            }
    	},

    	// 进入黑夜
    	sleep: function() {
    		this.message = '';
    		socket.emit('sleep');
        },
        
        // 预言家选择查看身份，狼人选择袭击目标
        select: function(id) {
            if (this.player.is_dead) {
                this.message = '你已死亡';
                return;
            }
            if (this.player.identity === 'prophet') {
                if (this.night) {
                    if (this.player.can_check_identity) {
                        socket.emit('identity', id);
                    }
                } else {
                    if (this.player.can_doubt) {
                        socket.emit('ticket', id);
                    }
                }
            } else if (this.player.identity === 'wolf') {
                if (this.night) {
                    if (this.player.can_attack) {
                        socket.emit('attack', id);
                    }
                } else {
                    if (this.player.can_doubt) {
                        socket.emit('ticket', id);
                    }
                }
            } else if (this.player.identity === 'witch') {
                if (this.night) {
                    if (this.player.can_use_poison) {
                        socket.emit('poison', id);
                    }
                } else {
                    if (this.player.can_doubt) {
                        socket.emit('ticket', id);
                    }
                }
            } else {
                if (this.player.can_doubt) {
                    socket.emit('ticket', id);
                }
            }
        },
        
        // 狼人选择袭击目标
        kill: function() {
        	
        },
        
        // 女巫使用灵药
        antidote: function(Yes) {
            socket.emit('antidote', Yes);
        },
        
        
    }
});

var socket = io();
socket.on('connect', _ => {
    app.message = '';
    document.body.style.background = '#fff';
    setTimeout(_ => {
        if (app.disconnected) {
            app.setName();
        }
    }, 200);
});

socket.on('disconnect', _ => {
    app.disconnected = true;
});

// 收到消息
socket.on('message', message => {
    app.message = message
});

// 初始化玩家
socket.on('players', (players, player) => {
    if (players) {
        app.players = players;
    }
    if (player) {
        app.player = player;
    }
});

// 预言家被唤醒
socket.on('prophet', _ => {
    document.body.style.background = '#006';
});

// 狼人被唤醒
socket.on('wolf', _ => {
    document.body.style.background = '#900';
});

// 女巫被唤醒
socket.on('witch', _ => {
    document.body.style.background = '#090';
    if (app.player.is_dead || (!app.player.can_use_poison && !app.player.can_use_antidote)) {
        setTimeout(_ => {
            socket.emit('witch_is_dead');
        }, 2000);
    }
});

socket.on('hunter', _ => {
    document.body.style.background = '#990';
});

// 狼人袭击目标
socket.on('attack', players => {
    app.players = players;
});

//进入黑夜
socket.on('night', _ => {
    app.night = true;
    document.body.style.background = '#000';
});

// 天亮了
socket.on('light', _ => {
    app.night = false;
    document.body.style.background = '#FFF';
});

socket.on('game_over', message => {
    app.player.is_dead = false;
    app.message = message;
});