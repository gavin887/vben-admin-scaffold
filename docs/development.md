# 开发与验证

依赖安装按锁文件执行。后端命令在 `backend/`，前端命令在 `frontend/`；下方组合检查从项目根目录开始。PHP 必须为 8.2+，命令使用 PATH 中的版本。

## Linux（Bash）

```bash
cd backend
composer validate --strict
find app config extend route tests public -type f -name '*.php' -print0 | xargs -0 -n 1 php -l
php tests/scaffold.php
cd ..
python3 scripts/http-smoke.py
cd frontend
pnpm run check:type
pnpm run build:admin
```

## Windows（PowerShell）

```powershell
Set-Location backend
composer validate --strict
Get-ChildItem app, config, extend, route, tests, public -Recurse -File -Filter *.php |
    ForEach-Object {
        php -l $_.FullName
        if ($LASTEXITCODE -ne 0) { throw "PHP syntax check failed: $($_.FullName)" }
    }
php tests/scaffold.php
Set-Location ..
python scripts/http-smoke.py
Set-Location frontend
pnpm run check:type
pnpm run build:admin
```

Windows 也可使用 Python 启动器 `py -3` 代替 `python`。HTTP 检查支持 `--php` 参数或 `PHP_BIN` 环境变量，适用于多个 PHP 版本共存的环境；选择自己的 PHP 8.2+ 可执行文件。

前端类型检查及构建通过 Turbo 编排，产物为 `frontend/apps/web-admin/dist/`。不要绕过 workspace 构建直接打包应用。运行前端单元测试可执行 `pnpm run test:unit`；格式和静态规则检查使用 `pnpm run lint`，按改动范围选择检查。

## 检查覆盖

`backend/tests/scaffold.php` 跳过真实 `.env` 并显式选择不存在的数据库驱动，检查权限策略、token 解析、未登录拒绝、结构 SQL、菜单页面、字段白名单和上传目录约定。它不执行真实查询、会话写入或上传。

`scripts/http-smoke.py` 启动临时 PHP 服务，在随机端口检查健康 JSON、未登录接口 HTTP/JSON 401、允许来源的 CORS 预检和未允许来源的响应头，最后停止服务。检查进程跳过 `.env`、禁用数据库连接，不会初始化数据表。

以上检查不能替代完整集成测试。配置独立测试库后，应检查登录、到期和退出、密码更改后旧会话失效、停用账号、动态菜单、普通角色越权拒绝、账号/角色/菜单/部门增删改查、设置和上传。验证时记录实际结果，不将静态检查当作运行验收。

## 常用开发位置

| 目的 | 位置 |
| --- | --- |
| 后端管理接口 | `backend/app/admin/controller/` |
| 数据模型 | `backend/app/admin/model/`、`backend/app/common/model/` |
| 权限策略 | `backend/app/common/service/AdminAccess.php` |
| 通用配置 | `backend/config/` |
| 前端页面 | `frontend/apps/web-admin/src/views/` |
| 前端请求 | `frontend/apps/web-admin/src/api/` |
| 开发代理 | `frontend/apps/web-admin/vite.config.mts` |

增加功能时同步更新接口、权限、前端 API 和菜单组件。对依赖执行 `composer audit` 及适合项目的前端依赖检查，评估兼容性后再升级锁文件。
