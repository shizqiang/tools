var app = new Vue({
    el: '#app',
    data: {
        name: 'kill wolf',
        night: false,
        players: [],
        player: {},
    },
    computed: {
    },
    created() {
    },
    methods: {

    	// 进入黑夜
    	sleep: function() {
    		socket.emit('sleep');
        },
        
        // 预言家选择查看身份，狼人选择袭击目标
        select: function(id) {
            if (this.player.is_dead) {
                alert('你已死亡');
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