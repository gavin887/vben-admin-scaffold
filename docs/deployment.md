# 部署说明

前后端分别构建和运行。PHP 内置服务器仅用于本地开发，生产使用支持 PHP 8.2+ 的 Web 服务与进程管理方式；前端由静态站点服务发布。

## 构建与安装

Linux（Bash），从项目根目录执行：

```bash
cd backend
composer install --no-dev --optimize-autoloader
cd ../frontend
pnpm install --frozen-lockfile
pnpm run build:admin
```

Windows（PowerShell），从项目根目录执行：

```powershell
Set-Location backend
composer install --no-dev --optimize-autoloader
Set-Location ../frontend
pnpm install --frozen-lockfile
pnpm run build:admin
```

前端发布目录为 `frontend/apps/web-admin/dist/`。构建时需要前端开发依赖；可在构建环境生成产物后只发布静态文件。PHP 服务的 Web 根目录设置为 `backend/public/`，通过 `public/index.php` 处理动态请求，不将源码根目录作为公开静态目录。

## 路径映射

同一站点域名下应提供以下路径：

| 请求 | 服务目标 |
| --- | --- |
| `/` 及前端路由 | 前端 dist，SPA 路由回退到前端 index.html |
| `/admin/*` | 后端 `/index.php/admin/*`，保留请求方法、查询串和 Authorization |
| `/uploads/*` | 后端 `public/uploads/*` 静态资源 |

这些映射需由 Nginx、Apache、IIS 或其他网关配置。Vite 的开发代理不包含在生产产物中。若使用不同 API 域名，需要调整前端构建的 API 地址和后端 CORS 来源；只放行实际前端来源。上传路径只提供静态读取，禁止 PHP 等脚本执行。

## 运行配置

按 [初始化说明](getting-started.md) 配置独立数据库和管理员。生产 `.env` 设置 `APP_DEBUG = false`，由部署环境保护数据库凭据。PHP 用户需要写入 `backend/runtime/` 和 `backend/public/uploads/`；仅给需要的用户授权。

使用 HTTPS，代理必须传递 Authorization。反向代理的上传大小限制应与接口限制协调：图片与 PDF 上限为 10 MB，视频上限为 100 MB，同时检查 PHP 的 `upload_max_filesize` 与 `post_max_size`。

发布前备份数据库与上传文件，运行 [开发与验证](development.md) 的适用检查，并在独立测试环境完成登录、角色权限和上传的运行验收。健康接口只反映基础服务响应，不代替数据库与完整功能监控。
