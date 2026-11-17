<?php
declare (strict_types=1);

namespace upload\engine;

use Exception;
use think\facade\Request;
use think\file\UploadedFile;

/**
 * 存储引擎抽象类
 * Class Base
 * @package upload\engine
 */
abstract class Base
{
    // 存储配置
    protected ?array $config;

    // file对象句柄
    /* @var $file UploadedFile */
    protected UploadedFile $file;

    // 存储路径
    protected string $path;
    // 存储文件名
    protected string $filename;

    /**
     * 构造函数
     * Server constructor.
     * @param array|null $config 存储配置
     */
    public function __construct(array $config = null)
    {
        $this->config = $config;
    }

    /**
     * 设置存储路径
     * @param string $path
     * @param string $filename
     * @return $this
     * @throws \RuntimeException
     */
    public function setPath(string $path, string $filename): Base
    {
        $this->path = $path;
        $this->filename = $filename;
        return $this;
    }

    /**
     * 设置上传的文件信息
     * @param string $name
     * @return $this
     * @throws \RuntimeException
     */
    public function setUploadFile(string $name): Base
    {
        // 接收上传的文件
        $this->file = Request::file($name);
        return $this;
    }

    /**
     * 获取文件信息
     * @return array
     */
    public function getFileInfo(): array
    {
        return [
            'domain' => $this->config['domain'] ?? '',     // 存储域名
            'extension' => strtolower($this->file->extension()),       // 文件扩展名
            'mime' => $this->file->getMime(),      // 文件类型
            'file_name' => $this->file->getOriginalName(), // 文件名称
            'path_name' => $this->file->getPathname(),     // 文件路径
            'md5' => $this->file->md5(),                   // 文件哈希值
        ];
    }

    /**
     * 文件上传
     * @return mixed
     */
    abstract protected function upload();

    /**
     * 文件删除
     * @param string $filePath
     * @return mixed
     */
    abstract protected function delete(string $filePath);

}
