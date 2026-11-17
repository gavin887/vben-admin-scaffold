<?php
namespace app\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\facade\Db;
class AdminCreate extends Command
{
    protected function configure()
    {
        $this->setName('admin:create')->addArgument('username',Argument::REQUIRED,'Administrator username')
            ->setDescription('Create administrator using SCAFFOLD_ADMIN_PASSWORD environment variable');
    }
    protected function execute(Input $input, Output $output)
    {
        $name=(string)$input->getArgument('username');$password=(string)getenv('SCAFFOLD_ADMIN_PASSWORD');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,64}$/',$name) || strlen($password)<12) {
            $output->writeln('<error>Use a 3-64 character username and SCAFFOLD_ADMIN_PASSWORD of at least 12 characters.</error>'); return 1;
        }
        if (Db::name('admin_user')->where('user_name',$name)->find()) {
            $output->writeln('<error>Administrator already exists; nothing was changed.</error>');return 1;
        }
        Db::name('admin_user')->insert(['user_name'=>$name,'password'=>password_hash($password,PASSWORD_DEFAULT),
            'nick_name'=>$name,'role_id'=>10001,'dept_id'=>10001,'status'=>1,'avatar'=>0,'create_time'=>time(),'update_time'=>time()]);
        $output->writeln('<info>Administrator created.</info>');return 0;
    }
}
