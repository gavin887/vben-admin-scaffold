<?php
namespace upload;

use upload\engine\Local;
use app\common\enum\file\Storage as StorageEnum;

class Storage
{

    // 当前存储引擎类
    private $engine;

    /**
     * 存储引擎类列表 (仅保留本地引擎)
     */
    const ENGINE_CLASS_LIST = [
        StorageEnum::LOCAL => Local::class,
    ];

    /**
     * 架构方法 设置参数
     * @access public
     */
    public function __construct(string $engine = StorageEnum::LOCAL, array $config = [])
    {
        $class = self::ENGINE_CLASS_LIST[$engine];
        $this->engine = new $class($config);
    }

    public function upload()
    {
        return $this->engine->upload();
    }

    /**
     * 执行文件删除
     * @param string $filePath
     * @return mixed
     */
    public function delete(string $filePath)
    {
        return $this->engine->delete($filePath);
    }

    /**
     * 设置存储路径
     * @param string $path
     * @param string $filename
     * @return mixed
     */
    public function setPath(string $path, string $filename)
    {
        return $this->engine->setPath($path, $filename);
    }

    /**
     * 设置上传的文件信息
     * @param string $name
     * @return mixed
     */
    public function setUploadFile(string $name)
    {
        return $this->engine->setUploadFile($name);
    }

    /**
     * 获取文件信息
     * @return array
     */
    public function getFileInfo(): array
    {
        return $this->engine->getFileInfo();
    }
}
