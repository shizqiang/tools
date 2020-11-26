<?php
namespace models;

use tasks\MailTask;
use libs\third\GoogleAuthenticator;
use libs\DB;
use libs\Lang;
use libs\Config;

class User extends Model {
    
    protected static $table = 'users';
    
    protected static $softDelete = false;
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $language;
    public $google_secret;
    public $credit;
    public $fund;
    public $buy_num;
    public $sell_num;
    public $accuse_count;
    public $argue_count;
    public $win_count;
    public $fail_count;
    public $register_time;
    public $is_active;
    public $active_time;
    public $last_login_time;
    public $last_business_time;
    public $out_wallet;
    public $wallet;
    public $wallet_key;
    
    /**
     * :重写store方法 
     * {@inheritDoc}
     * @see \models\Model::store()
     */
    public function store() {
        $user = User::find(['email =' => $this->email]);
        if ($user) {
            throw new \Exception(Lang::get('reg.email_exists'), 406);
        }
        $ga = new GoogleAuthenticator();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('4dealers', $secret);
        $this->google_secret = $secret;
        $this->register_time = date('Y-m-d H:i:s');
        $this->validate();
        $this->password = md5($this->password . $this->register_time);
        parent::store();
        
        // 产生信用记录 
        $userCredit = new UserCredit();
        $userCredit->credit = 100; // 注册赠送100 
        $userCredit->time = date('Y-m-d H:i:s');
        $userCredit->user_id = DB::MySQL()->lastInsertId();
        $userCredit->old_credit = 0;
        $userCredit->new_credit = 100;
        $userCredit->remark = '注册';
        $userCredit->store();
        
        // 发送激活邮件 
        $token = createNonceStr(60);
        $redis = DB::Redis();
        $redis->setex($token, 7200, serialize(['email' => $this->email, 'qrcode' => $qrCodeUrl]));
        $link = Config::get('app.url') . '/register?token=' . $token;
        $content = sprintf('<a href="%s">%s</a>', $link, $link);
        $mailTask = new MailTask($this->email, $content);
        $mailTask->dispach();
    }
    
    /**
     * 用户登录平台 
     */
    public function signin(string $passwd, string $oneCode) {
        $ga = new GoogleAuthenticator();
        $succ = $ga->verifyCode($this->google_secret, $oneCode, 2);
        if (!$succ) {
            throw new \Exception(Lang::get('signin.fail.oneCode'), 401);
        }
        if ($this->password !== md5($passwd . $this->register_time)) {
            throw new \Exception(Lang::get('signin.fail.passwd'), 401);
        }
    }
    
    /**
     * :激活账户 
     */
    public function active($oneCode) {
        $ga = new GoogleAuthenticator();
        $succ = $ga->verifyCode($this->google_secret, $oneCode, 2);
        if (!$succ) {
            throw new \Exception(Lang::get('reg.active_fail'), 406);
        }
        if ($this->is_active === 'Y') {
            throw new \Exception(Lang::get('reg.already.actived'), 406);
        }
        $this->is_active = 'Y';
        $this->active_time = date('Y-m-d H:i:s');
        $this->update();
    }
    
    /**
     * 用户修改密码
     */
    public function changePass($oldPass, $newPass) {
        if ($this->password !== md5($oldPass . $this->register_time)) {
            throw new \Exception(Lang::get('changePass.fail.oldpasswd'), 401);
        }
        $this->password = $newPass;
        $this->validate();
        $this->password = md5($this->password . $this->register_time);
        $this->update();
    }
    
    /**
     * :重写数据校验方法 
     * {@inheritDoc}
     * @see \models\Model::validate()
     */
    public function validate() {
        if (!$this->str($this->username, 6, 20)) {
            $this->_errors['username'] = 'invalid username';
        }
        if (!$this->str($this->email, 6, 40)) {
            $this->_errors['email'] = 'invalid email';
        }
        if (!$this->str($this->password, 6, 20)) {
            $this->_errors['password'] = 'invalid password';
        }
        parent::validate();
    }
    
}