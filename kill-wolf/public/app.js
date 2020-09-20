var app = new Vue({
    el: '#app',
    data: {
        name: 'kill wolf',
        players: [],
        player: {},
        sleeped: false,
        identity: '',
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
            if (app.identity === 'prophet') {
                socket.emit('identity', id);
            }
        	if (app.identity === 'wolf') {
                socket.emit('attack', id);
            }
            if (app.identity === 'witch') {
                socket.emit('poison', id);
            }
        },
        
        // 狼人选择袭击目标
        kill: function() {
        	
        },
        
        // 女巫使用灵药
        antidote: function(Yes) {
            socket.emit('antidote', Yes);
        },
        
        // 女巫使用毒药
        poison: function() {
        	
        },
        
        // 猎人枪杀
        shot: function() {
        	
        },
        
    }
});