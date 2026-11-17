# Vben Admin Scaffold frontend

Single Vben 5 / Vue 3 admin application and its reusable workspace packages.

```sh
pnpm install --frozen-lockfile
pnpm run dev:admin
pnpm run check:type
pnpm run build:admin
```

Development: http://localhost:5689. API `/admin` proxies to `http://127.0.0.1:8000/index.php/admin`. Build output: `apps/web-admin/dist`. Production needs the same `/admin` and `/uploads` reverse proxy routes. Configure app settings in `apps/web-admin/.env` and environment-specific files.

Build through the root Turbo command so shared build tooling is compiled before Vite. See the repository root documentation for Linux and Windows initialization and backend setup.

The original Vben MIT license is retained in LICENSE.
