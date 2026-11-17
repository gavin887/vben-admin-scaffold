<?php
declare(strict_types=1);
namespace app\admin\controller;
use think\response\Json;
use app\admin\model\admin\User;
use app\admin\model\admin\Admin;
use think\facade\Db;
class Auth extends Controller
{
    protected array $methodRules=['login'=>'POST','logout'=>'POST','refresh'=>'POST','profile'=>['GET','POST']];
    public function login(): Json
    {
        $model=new User;
        if (!$model->login($this->postData())) return $this->renderError($model->getError());
        return $this->renderSuccess(['accessToken'=>$model->getToken()]);
    }
    public function info(): Json
    {
        $info=$this->admin;
        $role=Db::name('admin_role')->where('admin_role_id',$info['role_id'])->find();
        $info['userId']=$info['admin_user_id'];$info['realName']=$info['nick_name'];
        $info['roles']=[$role['value']??''];$info['homePath']='/workspace';$info['desc']='';
        return $this->renderSuccess($info);
    }
    public function refresh(): Json
    {
        $model=new User;
        if (!$token=$model->refreshToken()) throw new \think\Exception('请重新登录',401);
        return $this->renderSuccess(['accessToken'=>$token]);
    }
    public function logout(): Json { (new User)->logout(); return $this->renderSuccess(); }
    public function profile(): Json
    {
        $model=Admin::detail((int)$this->admin['admin_user_id']);
        if ($this->request->isGet()) return $this->renderSuccess($model->toArray());
        $values=array_intersect_key($this->postForm()??[],array_flip(['nick_name','mobile','avatar','password']));
        if (!empty($values['password']) && strlen((string)$values['password'])<12) throw new \think\Exception('密码至少12位',422);
        if (!$model->edit($values)) return $this->renderError($model->getError());
        return $this->renderSuccess();
    }
}
