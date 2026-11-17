<?php
declare(strict_types=1);
namespace app\admin\controller;
use think\App;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;
use upload\Storage;
/** Local uploads only; images/videos and PDF documents are validated by content. */
class Upload extends Controller
{
    protected array $methodRules=['img'=>'POST','file'=>'POST','imagelist'=>'GET'];
    private Storage $storage;
    private array $fileinfo=[];
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->storage=new Storage('local',['domain'=>Request::domain()]);
        if (!Request::file('file')) return;
        $this->storage->setUploadFile('file');
        $this->fileinfo=$this->storage->getFileInfo();
        $this->storage->setPath('admin/'.$this->admin['admin_user_id'],$this->fileinfo['md5'].'.'.$this->fileinfo['extension']);
    }
    private function requireFile(): void
    {
        if (!$this->fileinfo) throw new \think\Exception('请选择上传文件',422);
    }
    public function img(): Json
    {
        $this->requireFile();
        $types=['jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'png'=>['image/png'],'gif'=>['image/gif'],'webp'=>['image/webp'],'bmp'=>['image/bmp','image/x-ms-bmp'],'mp4'=>['video/mp4'],'webm'=>['video/webm']];
        $ext=$this->fileinfo['extension'];$mime=$this->fileinfo['mime'];
        if (!isset($types[$ext]) || !in_array($mime,$types[$ext],true)) throw new \think\Exception('文件类型错误',422);
        $limit=str_starts_with($mime,'image/')?10:100;
        if (filesize($this->fileinfo['path_name'])>$limit*1024*1024) throw new \think\Exception('文件过大',422);
        return $this->uploadResponse();
    }
    public function file(): Json
    {
        $this->requireFile();
        $fp=fopen($this->fileinfo['path_name'],'rb');$signature=$fp?fread($fp,5):'';if ($fp) fclose($fp);
        if ($this->fileinfo['extension']!=='pdf' || $this->fileinfo['mime']!=='application/pdf' || $signature!=='%PDF-') throw new \think\Exception('仅支持PDF文档',422);
        if (filesize($this->fileinfo['path_name'])>10*1024*1024) throw new \think\Exception('文件过大',422);
        return $this->uploadResponse();
    }
    private function uploadResponse(): Json
    {
        return json(array_merge(['code'=>200,'message'=>'上传成功'],$this->uploadfile()));
    }
    private function uploadfile(): array
    {
        $filter=['type'=>'admin','user_id'=>$this->admin['admin_user_id'],'md5'=>$this->fileinfo['md5']];
        $info=Db::name('upload')->where($filter)->find();
        if (!$info) {
            $result=$this->storage->upload();
            if (!is_array($result)) throw new \RuntimeException('上传失败');
            $info=['type'=>'admin','user_id'=>$this->admin['admin_user_id'],'storage'=>'local','url'=>$result['path'],
                'filename'=>mb_substr($this->fileinfo['file_name'],0,63),'filetype'=>$this->fileinfo['extension'],
                'filesize'=>filesize($this->fileinfo['path_name']),'mimetype'=>$this->fileinfo['mime'],
                'create_time'=>gmdate('Y-m-d H:i:s'),'update_time'=>gmdate('Y-m-d H:i:s'),'md5'=>$this->fileinfo['md5']];
            $info['id']=Db::name('upload')->insertGetId($info);
        }
        return ['id'=>$info['id'],'name'=>$info['filename'],'status'=>'done','url'=>Request::domain().$info['url']];
    }
    public function imagelist(): Json
    {
        $list=Db::name('upload')->where(['type'=>'admin','user_id'=>$this->admin['admin_user_id']])
            ->where(function($query) { $query->whereLike('mimetype','image/%')->whereOr('mimetype','like','video/%'); })
            ->order('update_time','desc')->paginate(min(100,max(1,(int)input('pageSize',20))))
            ->each(function($item) { $item['url']=Request::domain().$item['url'];return $item; })->toArray();
        return $this->renderSuccess(['total'=>$list['total'],'items'=>$list['data'],'engine'=>'local']);
    }
}
