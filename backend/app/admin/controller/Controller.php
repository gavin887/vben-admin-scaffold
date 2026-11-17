<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\Request;
use think\App;
use think\Validate;
use think\response\Json;
use think\exception\ValidateException;
use app\admin\model\admin\User as UserModel;
use app\common\service\AdminAccess;

class Controller
{
    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    // 登录信息
    protected $admin;

    // 当前控制器名称
    protected string $controller = '';

    // 当前方法名称
    protected string $action = '';

    // 当前路由uri
    protected string $routeUri = '';

    // 当前路由：分组名称
    protected string $group = '';

    // 登录验证白名单
    protected array $allowAllAction = [
        // 登录页面
        'auth/login',
    ];

    /**
     * 强制验证当前访问的控制器方法method
     * 例: [ 'login' => 'POST' ]
     * @var array
     */
    protected array $methodRules = ['add'=>'POST','edit'=>'POST','delete'=>'POST','setstatus'=>'POST'];

    /**
     * 构造方法
     * BaseController constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     * @return void
     * @throws BaseException
     */
    public function initialize()
    {
        // 当前路由信息
        $this->getRouteinfo();
        // 无凭证及公开登录请求在构造 ORM 模型前返回，避免隐式连接数据库。
        $this->setAdminInfo();
        // 验证登录
        $this->checkLogin();
        // 接口级权限校验
        $this->checkAccess();
        // 强制验证当前访问的控制器方法method
        $this->checkMethodRules();
    }

    /**
     * 设置管理员登录信息
     */
    private function setAdminInfo()
    {
        $this->admin = false;
        if (in_array($this->routeUri, $this->allowAllAction, true) || UserModel::requestToken() === '') {
            return;
        }
        $model = new UserModel();
        $this->admin = $model->getLoginInfo();
    }

    /**
     * 解析当前路由参数 （分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称
        $this->controller = uncamelize($this->request->controller());
        // 方法名称
        $this->action = $this->request->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri = "{$this->controller}/$this->action";
    }

    /**
     * 验证登录状态
     * @return void
     */
    private function checkLogin(): void
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return;
        }
        // 验证登录状态
        if (empty($this->admin)) {
            throw new \think\Exception('请先登录后再访问', config('status.not_logged'));
        }
    }

    /**
     * 接口级权限校验
     * 白名单 / 未登录 跳过 (checkLogin 已处理未登录)
     * @return void
     */
    private function checkAccess(): void
    {
        // 白名单路由跳过
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return;
        }
        // 未登录跳过 (checkLogin 会拦截)
        if (empty($this->admin)) {
            return;
        }
        AdminAccess::check(
            (int)($this->admin['role_id'] ?? 0),
            $this->controller,
            $this->action,
            $this->request->method()
        );
    }

    /**
     * 强制验证当前访问的控制器方法method
     */
    private function checkMethodRules(): void
    {
        if (!isset($this->methodRules[$this->action])) {
            return;
        }
        $methodRule = $this->methodRules[$this->action];
        $currentMethod = $this->request->method();
        if (empty($methodRule)) {
            return;
        }
        if (is_array($methodRule) && in_array($currentMethod, $methodRule)) {
            return;
        }
        if (is_string($methodRule) && $methodRule == $currentMethod) {
            return;
        }
        throw new \think\Exception('illegal request method', 405);
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int|null $status
     * @param string $message
     * @param array $data
     * @return Json
     */
    protected final function renderJson(int $code = null, string $message = '', array $data = []): Json
    {
        if ($code === 200 && $this->request->isPost() && !empty($this->admin) && $this->controller !== 'auth') {
            \app\common\service\AuditLog::record((int)$this->admin['admin_user_id'], $this->controller, $this->action);
        }
        return json(compact('code', 'message', 'data'), $code);
    }

    /**
     * 返回操作成功json
     * @param array|string $data
     * @param string $message
     * @return Json
     */
    protected final function renderSuccess($data = [], string $message = 'success'): Json
    {
        if (is_string($data)) {
            $message = $data;
            $data = [];
        }
        return $this->renderJson(config('status.success'), $message, $data);
    }

    /**
     * 返回操作失败json
     * @param string $message
     * @param array $data
     * @return Json
     */
    protected final function renderError(string $message = 'error', array $data = []): Json
    {
        return $this->renderJson(config('status.error'), $message, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param null $key
     * @param bool $filter
     * @return mixed
     */
    protected final function postData($key = null, bool $filter = false)
    {
        return $this->request->post(empty($key) ? '' : "{$key}/a", null, $filter ? '' : null);
    }

    /**
     * 获取post数据 (数组)
     * @param string|null $key
     * @param bool $filter
     * @return mixed
     */
    protected final function postForm(?string $key = 'form', bool $filter = true)
    {
        return $this->postData(empty($key) ? 'form' : $key, $filter);
    }
}
