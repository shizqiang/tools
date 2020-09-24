function Player(id, name) {
	this.id = id;
	this.name = name;
	this.offline = false; // 玩家是否掉线
	this.sleep = false; // 玩家是否睡着
	
	this.is_killed_tonight = false; // 是否今晚被狼杀
	this.is_poison_tonight = false; // 是否今晚被女巫毒死

	this.is_dead = false; // 是否死亡
	this.is_police = false; // 是否是警长
	this.identity = ''; // 身份

	this.can_check_identity = false; // 预言家是否可查看别人身份 

	this.can_attack = false; // 狼人是否可以袭击玩家
	this.attack_id = ''; // 狼人袭击的人
	this.votes = []; // 被袭击的狼人列表

	this.can_doubt = false; // 是否可以投票
	this.doubt_id = ''; // 投谁的票
	this.tickets = []; // 被投票的玩家列表 
	
	this.has_antidote = false; // 有灵药
	this.has_poison = false; // 有毒药
	this.can_use_antidote = false; // 可以使用灵药
	this.can_use_poison = false; // 可以使用毒药

	this.can_shot = false; // 猎人可以开枪

	this.last_words = false; // 是否可以有遗言
};

module.exports = Player;
