# Vben Admin Scaffold

基于 ThinkPHP 8、Vben 5 和 Vue 3 的通用管理后台脚手架，提供账号登录、会话、角色与菜单权限、部门、个人资料、基础设置和本地上传功能。

```text
backend/   ThinkPHP API、配置、数据库结构与后端测试
frontend/  Vben 管理应用、公共组件、pnpm workspace 与构建工具
docs/      初始化、架构、开发验证与部署说明
scripts/   HTTP 冒烟检查工具
```

## 环境要求

| 工具 | 版本 / 要求 |
| --- | --- |
| PHP | 8.2+，启用 PDO MySQL、mbstring、fileinfo、GD |
| Composer | 2.x |
| MySQL | 8.x |
| Node.js | 20.10+ |
| pnpm | 10.14.0，与 `frontend/package.json` 一致 |
| Python | 3.11+，仅 HTTP 冒烟检查需要 |

将工具加入操作系统的 PATH，确认 `php -v`、`composer --version`、`node --version` 和 `pnpm --version` 可用。使用 Corepack 时可运行 `corepack enable` 和 `corepack prepare pnpm@10.14.0 --activate`；没有 Corepack 时可安装 `npm install --global pnpm@10.14.0`。

## 初始化与启动

按 [初始化说明](docs/getting-started.md) 选择 Linux（Bash）或 Windows（PowerShell）步骤，完成：

1. 克隆项目并安装后端依赖。
2. 创建独立 MySQL 空库和数据库账号，填写 `backend/.env`。
3. 初始化数据表并创建首个管理员。项目不提供默认管理员密码。
4. 启动后端与前端。

开发服务默认地址为：

| 服务 | 地址 |
| --- | --- |
| 前端 | `http://localhost:5689` |
| 后端健康检查 | `http://127.0.0.1:8000/health` |
| 管理 API | `http://127.0.0.1:8000/index.php/admin` |

前端请求相对地址 `/admin`，开发服务器代理到后端管理 API；`/uploads` 代理到后端上传目录。允许的前端来源配置位于 `backend/config/cors.php`。

## 开发与部署

- [初始化说明](docs/getting-started.md)：Linux 和 Windows 的数据库、管理员及服务初始化。
- [架构说明](docs/architecture.md)：目录、认证权限、数据模型和请求路径。
- [开发与验证](docs/development.md)：类型检查、构建、后端测试及 HTTP 冒烟检查。
- [部署说明](docs/deployment.md)：生产构建、Web 根目录、代理和运行目录。

前端构建使用 `pnpm run build:admin`，产物位于 `frontend/apps/web-admin/dist/`。依赖由 Composer 和 pnpm 锁文件安装，依赖目录、缓存、日志、上传文件与构建产物不进入版本控制。

## 依赖说明

`league/flysystem 1.1.10` 存在低危公告 [CVE-2026-102601](https://github.com/advisories/GHSA-cxf4-7mrp-vvpr)，其版本受 ThinkPHP 文件系统适配层约束。上传目录及文件名由服务端 ID 和 MD5 生成，避免将用户输入直接用作存储路径；仍需跟踪上游兼容修复并执行 `composer audit`。该限制不应视为已完成依赖修复。

后端采用 [Apache-2.0](backend/LICENSE)，并保留 [ThinkPHP 版权与许可说明](backend/NOTICE)；前端保留 [Vben MIT 许可证](frontend/LICENSE)。第三方依赖遵循各自许可证。
