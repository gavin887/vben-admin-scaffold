<?php
declare(strict_types=1);
namespace app\admin\model\admin;
use app\common\model\admin\User as UserModel;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
/** Opaque revocable sessions; passwords never enter tokens. */
class User extends UserModel
{
    protected $hidden = ['password'];
    private string $token = '';
    public function getAvatarAttr($value)
    {
        if (empty($value)) return '';
        return get_file_url($value)[0]['url'] ?? '';
    }
    public function login(array $params)
    {
        $username=trim((string)($params['username']??'')); $password=(string)($params['password']??'');
        if ($username==='' || $password==='') return $this->setError('请输入用户名和密码');
        $key='admin_login_'.hash('sha256',Request::ip().':'.$username);
        $attempts=(int)Cache::get($key,0);
        if ($attempts>=5) throw new \think\Exception('登录失败次数过多，请稍后重试',429);
        $user=static::where(['user_name'=>$username,'status'=>1])->find();
        if (!$user || !password_verify($password,(string)$user['password'])) {
            Cache::set($key,$attempts+1,900); return $this->setError('登录失败，用户名或密码错误');
        }
        Cache::delete($key); $this->issue($user); return $user;
    }
    private function issue($user): void
    {
        $this->token=bin2hex(random_bytes(32));
        Db::name('admin_token')->where('expires_at','<=',time())->delete();
        Db::name('admin_token')->insert(['token_hash'=>hash('sha256',$this->token),
            'admin_user_id'=>(int)$user['admin_user_id'],'password_hash'=>hash('sha256',(string)$user['password']),'expires_at'=>time()+28800]);
    }
    public function getLoginInfo()
    {
        $token=$this->getToken(); if ($token==='') return false;
        $session=Db::name('admin_token')->where('token_hash',hash('sha256',$token))->find();
        if (!$session || (int)$session['expires_at']<=time()) return false;
        $user=static::where(['admin_user_id'=>$session['admin_user_id'],'status'=>1])->find();
        if (!$user || !hash_equals((string)$session['password_hash'],hash('sha256',(string)$user['password']))) return false;
        return $user->toArray();
    }
    public function refreshToken()
    {
        if (!$user=$this->getLoginInfo()) return false;
        $original=static::find((int)$user['admin_user_id']); $this->logout(); $this->issue($original); return $this->token;
    }
    public function logout(): void
    {
        $token=$this->getToken();
        if ($token!=='') Db::name('admin_token')->where('token_hash',hash('sha256',$token))->delete();
        $this->token='';
    }
    public function getToken(): string
    {
        if ($this->token!=='') return $this->token;
        return self::requestToken();
    }
    public static function requestToken(): string
    {
        $header=(string)Request::header('Authorization','');
        if (!preg_match('/^Bearer ([a-f0-9]{64})$/i',$header,$matches)) return '';
        return $matches[1];
    }
}
