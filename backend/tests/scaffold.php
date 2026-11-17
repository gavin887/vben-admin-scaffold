<?php
declare(strict_types=1);

// No database server is required. Any accidental connection uses an undefined driver.
require dirname(__DIR__) . '/vendor/autoload.php';

$app = new think\App(dirname(__DIR__) . DIRECTORY_SEPARATOR);
$app->setEnvName('scaffold_test_' . bin2hex(random_bytes(8)));
$app->env->set('DB_DRIVER', 'scaffold_test_no_database');
$app->initialize();
set_exception_handler(static function (Throwable $exception): void {
    fwrite(STDERR, get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
});
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}

use app\common\service\AdminAccess;
use app\admin\model\admin\User;

check(config('database.default') === 'scaffold_test_no_database', 'tests cannot connect to any configured database');
check(AdminAccess::allows('supperadmin', 'admin', 'delete', []), 'super administrator access');
check(AdminAccess::allows('custom', 'admin', 'lists', [10004]), 'user menu grants user administration');
check(!AdminAccess::allows('custom', 'role', 'delete', [10004]), 'user menu cannot modify roles');
check(!AdminAccess::allows('custom', 'setting', 'edit', [10003]), 'parent menu does not grant all system administration');
check(!AdminAccess::allows('custom', 'unknown', 'lists', [10004]), 'unknown controllers denied');
check(AdminAccess::allows('custom', 'auth', 'profile', []), 'authenticated profile access');
check(AdminAccess::allows('custom', 'menu', 'lists', []), 'own navigation is available');
check(!AdminAccess::allows('custom', 'menu', 'edit', []), 'navigation access does not grant menu edits');
check(AdminAccess::allows('custom', 'role', 'all', [10004]), 'user form role options available');
check(AdminAccess::allows('custom', 'role', 'all', [10006]), 'role form options available');
check(AdminAccess::allows('custom', 'dept', 'trees', [10008]), 'department tree available with department permission');
check(!AdminAccess::allows('custom', 'dept', 'trees', [10006]), 'unrelated role menu cannot fetch department tree');

$request = $app->request;
foreach (['', 'Bearer debug', 'Bearer default', 'Basic ' . str_repeat('a',64), 'Bearer ' . str_repeat('a',63)] as $header) {
    $request->withHeader(['Authorization'=>$header]);
    check(User::requestToken() === '', 'malformed token must be empty');
    $request->setController('Auth')->setAction('info')->setMethod('GET');
    try {
        new app\admin\controller\Auth($app);
        check(false, 'missing token should reject');
    } catch (think\Exception $exception) {
        check($exception->getCode() === 401, 'missing token returns 401 before any model/database initialization');
    }
}
$request->withHeader(['Authorization'=>'Bearer ' . str_repeat('a',64)]);
check(User::requestToken() === str_repeat('a',64), 'valid opaque token parses without database');
$request->setController('Auth')->setAction('login')->setMethod('GET');
try {
    new app\admin\controller\Auth($app);
    check(false, 'login GET should reject');
} catch (think\Exception $exception) {
    check($exception->getCode() === 405, 'public login ignores stale token before model construction');
}

$reflection = new ReflectionClass(app\command\MigrateRun::class);
$command = $reflection->newInstanceWithoutConstructor();
$split = $reflection->getMethod('splitStatements');
$sql = "-- excluded comment\r\nINSERT INTO `a;b` VALUES ('one;two', 'it''s;fine');\nSELECT \"x;y\";";
check(count($split->invoke($command, $sql)) === 2, 'migration splitting preserves quoted semicolons');
$schema = file_get_contents(dirname(__DIR__) . '/database/migrations/0001_scaffold.sql');
preg_match_all('/CREATE TABLE `__PREFIX__([^`]+)`/', $schema, $matches);
$tables = $matches[1]; sort($tables);
$expected = ['admin_dept','admin_menu','admin_role','admin_role_menu','admin_token','admin_user','audit_log','setting','upload'];
sort($expected);
check($tables === $expected, 'only generic scaffold tables are installed');
check(!preg_match('/\b(DROP|DELETE|TRUNCATE)\b/i', $schema), 'initial migration has no destructive statements');
check(!preg_match('/INSERT INTO `__PREFIX__admin_user`/i', $schema), 'no administrator or password seeded');
check(!str_contains($schema, "`role_id` int(11) unsigned NOT NULL DEFAULT '10001'"), 'omitted role never defaults to super administrator');
check(count($split->invoke($command, $schema)) === 21, 'all clean migration statements parse');
preg_match_all("/'(\/(?:system|dashboard)\/[^']+\/index)'/", $schema, $components);
foreach ($components[1] as $component) {
    check(is_file(dirname(__DIR__,2) . '/frontend/apps/web-admin/src/views' . $component . '.vue'), 'seed menu component exists: ' . $component);
}
check(class_exists(upload\engine\Local::class), 'local upload engine autoloads');
check(config('filesystem.disks.public.url') === '/uploads', 'uploaded URLs match the frontend proxy');
check(str_replace('\\','/',config('filesystem.disks.public.root')) === str_replace('\\','/',dirname(__DIR__)) . '/public/uploads', 'upload disk matches the documented public directory');
foreach ([[], ['role_id'=>0], ['role_id'=>[]], ['role_id'=>'10001abc']] as $invalid) {
    try {
        app\admin\model\admin\Admin::sanitize($invalid, true);
        check(false, 'new accounts require explicit valid role IDs');
    } catch (think\Exception $exception) {
        check($exception->getCode() === 422, 'missing/malformed role rejected without database');
    }
}
$clean = app\admin\model\admin\Admin::sanitize(['role_id'=>'10002','admin_user_id'=>10001,'role'=>['value'=>'supperadmin'],'create_time'=>1],true);
check($clean === ['role_id'=>10002], 'primary keys, timestamps and nested relations cannot be mass assigned');
foreach (['Role'=>'admin_role_id','Menu'=>'admin_menu_id','Dept'=>'admin_dept_id'] as $model=>$pk) {
    $class = 'app\\admin\\model\\admin\\' . $model;
    check($class::writable([$pk=>10001,'create_time'=>1,'unknown'=>true]) === [], $model . ' primary keys and timestamps cannot be mass assigned');
}
$menuDefaults = (new ReflectionClass(app\common\model\admin\Menu::class))->getDefaultProperties();
check(in_array('meta', $menuDefaults['json'], true) && $menuDefaults['jsonAssoc'], 'menu metadata returns associative JSON');
$settingDefaults = (new ReflectionClass(app\common\model\Setting::class))->getDefaultProperties();
check(in_array('values', $settingDefaults['json'], true) && $settingDefaults['createTime'] === false, 'setting values JSON matches schema');
echo "Scaffold checks passed: {$checks}\n";
