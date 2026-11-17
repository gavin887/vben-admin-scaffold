# 初始化说明

需要 PHP 8.2+（PDO MySQL、mbstring、fileinfo、GD）、Composer 2、MySQL 8.x、Node.js 20.10+ 和 pnpm 10.14.0。下述 Linux 与 Windows 步骤均从项目根目录开始，命令依赖工具已加入 PATH。不要在已有数据的数据库中执行首次初始化。

## 准备数据库

使用具备创建数据库、用户和授权权限的 MySQL 管理账号，在 Linux 终端或 Windows PowerShell 中运行：

```text
mysql -u root -p
```

在 MySQL 提示符执行以下 SQL，先将 `REPLACE_WITH_A_STRONG_PASSWORD` 替换为自己的数据库密码。此密码用于数据库连接，与后台管理员密码不同。

```sql
CREATE DATABASE vben_scaffold CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'scaffold'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_A_STRONG_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX
  ON vben_scaffold.* TO 'scaffold'@'127.0.0.1';
EXIT;
```

示例让 PHP 通过 `127.0.0.1` 连接同机 MySQL。远程数据库需要使用实际服务地址，并按应用服务器的来源地址创建和授权账号。初始化需要建表权限；生产环境可使用单独的结构管理账号，应用运行账号仅保留所需的数据读写权限。后续结构变更若需要更多 DDL 权限，应单独评估授权。

## Linux（Bash）

### 1. 获取代码和安装依赖

```bash
git clone https://github.com/gavin887/vben-admin-scaffold.git
cd vben-admin-scaffold
cd backend
composer install
[ -f .env ] || cp .env.example .env
```

使用编辑器打开 `backend/.env`，填写下方配置。保留样例中的其他设置，密码含特殊字符时按 INI 语法使用引号。

```ini
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = vben_scaffold
DB_USER = scaffold
DB_PASS = "YOUR_DATABASE_PASSWORD"
DB_PREFIX = xfs_
APP_DEBUG = false
```

`backend/runtime/` 和 `backend/public/uploads/` 需要对运行 PHP 的用户可写。开发环境可在项目根执行 `mkdir -p backend/runtime backend/public/uploads`；部署时由服务管理者为 PHP 用户设置目录权限，不要使用全员可写权限。

### 2. 初始化表并创建管理员

在 `backend/` 执行：

```bash
php think migrate:run
php think migrate:status
(
  trap 'unset SCAFFOLD_ADMIN_PASSWORD' EXIT
  read -r -s -p '初始管理员密码（至少12位）: ' SCAFFOLD_ADMIN_PASSWORD
  printf '\n'
  export SCAFFOLD_ADMIN_PASSWORD
  php think admin:create admin
)
```

密码通过进程环境传给创建命令，不写入命令参数，子 Shell 退出后清理。`admin` 是要创建的用户名，可换为 3—64 位字母、数字、下划线、点或连字符。创建命令不会覆盖同名账号，执行失败时先检查输出，再继续启动服务。

### 3. 启动后端

在 `backend/` 保持该终端运行：

```bash
php -S 127.0.0.1:8000 -t public public/router.php
```

### 4. 启动前端

新开终端，进入项目根目录后运行：

```bash
cd frontend
pnpm install --frozen-lockfile
pnpm run dev:admin
```

## Windows（PowerShell）

### 1. 获取代码和安装依赖

```powershell
git clone https://github.com/gavin887/vben-admin-scaffold.git
Set-Location vben-admin-scaffold
Set-Location backend
composer install
if (-not (Test-Path -LiteralPath .env)) {
    Copy-Item -LiteralPath .env.example -Destination .env
}
```

使用编辑器填写 `backend/.env`，字段与 Linux 示例相同。确认 `DB_PASS` 是所创建数据库用户的密码。PHP、Composer、Node.js 和 pnpm 均使用 PATH 中的版本，无需特定安装目录。

在项目根创建运行目录，并在 Windows 文件夹权限中允许 PHP 服务账号写入：

```powershell
New-Item -ItemType Directory -Path backend/runtime, backend/public/uploads -Force | Out-Null
```

### 2. 初始化表并创建管理员

在 `backend/` 执行：

```powershell
php think migrate:run
php think migrate:status
$adminSecret = Read-Host '初始管理员密码（至少12位）' -AsSecureString
try {
    $env:SCAFFOLD_ADMIN_PASSWORD = [System.Net.NetworkCredential]::new('', $adminSecret).Password
    php think admin:create admin
} finally {
    Remove-Item Env:\SCAFFOLD_ADMIN_PASSWORD -ErrorAction SilentlyContinue
    $adminSecret.Dispose()
}
```

密码不会显示在终端或命令参数中，完成后清除进程环境变量。用户名约束与 Linux 相同。PowerShell 中外部命令非零退出码不会自动停止后续操作；每个初始化命令执行后先确认成功输出，必要时检查 `$LASTEXITCODE`。

### 3. 启动后端

在 `backend/` 保持该终端运行：

```powershell
php -S 127.0.0.1:8000 -t public public/router.php
```

### 4. 启动前端

新开 PowerShell，进入项目根目录后运行：

```powershell
Set-Location frontend
pnpm install --frozen-lockfile
pnpm run dev:admin
```

## 访问与检查

打开 `http://localhost:5689`，使用创建的管理员用户名和密码登录。`http://127.0.0.1:8000/health` 返回健康 JSON，但健康响应成功不能证明数据库连接和表结构可用。

首次初始化包含 9 张通用表，以及基础角色、部门和菜单；结构执行命令另建一张记录执行文件的 `migration` 表。不包含管理员账号或默认密码。SQL 文件包含 DDL，部分失败时不能通过事务整体回滚，应检查错误和实际表结构后处理，避免直接重复执行已部分成功的文件。

开发请求路径和 CORS 设置见 [架构说明](architecture.md)。运行检查见 [开发与验证](development.md)，生产配置见 [部署说明](deployment.md)。
