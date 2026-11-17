# 项目协作约定

## 目录与工具

- `backend/`：PHP 8.2+ / ThinkPHP 8；在该目录运行 Composer 和 `php think`。
- `frontend/`：Vben 5 / Vue 3 / pnpm workspace；使用 `packageManager` 指定的 pnpm 10.14.0，在该目录执行 pnpm 命令。
- `docs/`：面向开发者的初始化、架构、验证与部署说明。
- `scripts/`：跨平台项目检查工具。

所有说明和脚本应支持 Linux 与 Windows，使用 PATH 中的工具或显式参数，不写入个人目录、盘符、工具安装位置或本机凭据。初始化方式见 `docs/getting-started.md`。

## 开发约定

保持现有 ThinkPHP/Vben 结构，优先复用公共组件、请求封装、异常处理和权限逻辑。接口遵循 `{code, message, data}` 响应约定；功能变更应同步检查后端接口、前端 API、路由、权限和文档。上传接口的响应字段还包括 `id`、`name`、`status`、`url`，修改时需要检查上传组件。

前端构建通过根脚本及 Turbo 编排，产物限定在 `frontend/apps/web-admin/dist/`。开发代理路径为 `/admin` 和 `/uploads`；生产环境需要配置对应映射。

真实 `.env`、密码、token、私钥、数据库备份、日志和上传文件不得提交。环境样例只填写通用默认值或占位凭据。数据库初始化仅在配置明确的独立空库执行，测试不得自动初始化或清理共享数据库。

多执行者协作时先约定文件范围；共享接口变化需同步沟通，数据库集成测试不得并发写同一个库。删除或移动目录前确认目标处于项目内，并使用当前操作系统的原生文件操作。

## 验证

按改动范围运行检查，具体 Linux / Windows 命令见 `docs/development.md`：

- 后端：PHP 语法检查、`php tests/scaffold.php`、`composer validate --strict`。
- 前端：`pnpm run check:type`、`pnpm run build:admin`。
- HTTP：在项目根运行 `scripts/http-smoke.py`，需要 Python 3.11+ 和 PHP 8.2+。

无数据库测试与 HTTP 冒烟检查不能替代真实登录、CRUD、权限和上传的集成测试。交付时如实说明执行的检查和未验证的运行条件。
