"""Run database-free HTTP checks against a temporary backend server."""
import argparse
import json
import os
from pathlib import Path
import socket
import subprocess
import tempfile
import time
import uuid
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[1]


def run(php):
    backend = ROOT / 'backend'
    if not (backend / 'vendor/autoload.php').is_file():
        raise RuntimeError('Restore backend dependencies with Composer first.')
    version = subprocess.check_output([php, '-r', 'echo PHP_VERSION_ID;'], text=True)
    if int(version.strip()) < 80200:
        raise RuntimeError('PHP 8.2+ is required; pass --php with the correct executable.')
    with socket.socket() as listener:
        listener.bind(('127.0.0.1', 0))
        port = listener.getsockname()[1]
    base = f'http://127.0.0.1:{port}'

    def request(path, method='GET', origin=None):
        headers = {'Accept': 'application/json'}
        if origin:
            headers['Origin'] = origin
        if method == 'OPTIONS':
            headers['Access-Control-Request-Method'] = 'GET'
            headers['Access-Control-Request-Headers'] = 'authorization,content-type'
        req = Request(base + path, headers=headers, method=method)
        try:
            response = urlopen(req, timeout=3)
        except HTTPError as error:
            response = error
        with response:
            return response.status, response.headers, response.read()

    def require(condition, message):
        if not condition:
            raise AssertionError(message)

    # Skip real .env files and deliberately select a nonexistent DB connection.
    # Any regression that touches the database must fail without a network connection.
    smoke_env = os.environ.copy()
    env_name = 'http_smoke_' + uuid.uuid4().hex
    if (backend / ('.env.' + env_name)).exists():
        raise RuntimeError('Unexpected smoke environment file exists.')
    for key, value in {'ENV_NAME': env_name, 'DB_DRIVER': '__http_smoke_no_database__',
                       'APP_DEBUG': 'false'}.items():
        smoke_env[key] = value
        smoke_env['PHP_' + key] = value

    # These requests never authenticate or migrate; do not target an existing server.
    with tempfile.TemporaryFile() as log:
        process = subprocess.Popen(
            [php, '-S', f'127.0.0.1:{port}', '-t', 'public', 'public/router.php'],
            cwd=backend, stdout=log, stderr=log, env=smoke_env,
            creationflags=subprocess.CREATE_NO_WINDOW if os.name == 'nt' else 0,
        )
        try:
            for attempt in range(100):
                if process.poll() is not None:
                    raise RuntimeError('Temporary PHP server exited before becoming ready.')
                try:
                    status, headers, body = request('/')
                    break
                except (URLError, TimeoutError, ConnectionError):
                    time.sleep(0.1)
            else:
                raise RuntimeError('Temporary PHP server did not become ready.')
            payload = json.loads(body)
            require(status == 200 and payload.get('code') == 200,
                    'Health endpoint must return a success JSON envelope without a database.')
            print('PASS: database-free health JSON')

            status, headers, body = request('/index.php/admin/auth/info')
            payload = json.loads(body)
            require(status == 401 and payload.get('code') == 401,
                    'Unauthenticated admin request must return HTTP/JSON 401.')
            require(not any(key in payload for key in ('trace', 'file', 'line', 'traces')),
                    'Unauthenticated response must not expose stack details.')
            print('PASS: unauthenticated admin rejection')

            origin = 'http://localhost:5689'
            status, headers, body = request('/index.php/admin/auth/info', 'OPTIONS', origin)
            require(status == 204 and headers.get('Access-Control-Allow-Origin') == origin,
                    'Allowed local frontend origin must pass CORS preflight.')
            print('PASS: allowed CORS preflight')

            status, headers, body = request('/', origin='https://untrusted.invalid')
            require(headers.get('Access-Control-Allow-Origin') is None,
                    'Unlisted origin must not receive an allow-origin header.')
            print('PASS: unlisted CORS origin blocked')
        finally:
            process.terminate()
            try:
                process.wait(timeout=5)
            except subprocess.TimeoutExpired:
                process.kill()
                process.wait(timeout=5)
    print('4 HTTP checks passed; temporary server stopped. Database connection disabled; no migration was run.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', default=os.environ.get('PHP_BIN', 'php'))
    args = parser.parse_args()
    try:
        run(args.php)
    except (OSError, ValueError, RuntimeError, AssertionError) as error:
        parser.exit(1, f'HTTP smoke failed: {error}\n')
