<?php
declare (strict_types=1);

namespace upload\engine;

class Local extends Base
{
    /**
     * 上传文件
     * @return array|string
     */
    public function upload(): array|string
    {
        try {
            $path = rtrim((string)config('filesystem.disks.public.url'), '/') . '/' . \think\facade\Filesystem::disk('public')->putFileAs($this->path, $this->file, $this->filename);
            $fileInfo = $this->getFileInfo();
            if(in_array($fileInfo['mime'],['image/jpeg','image/png','image/gif','image/webp'])){
                $this->resizeAndCompressImage(public_path().$path);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return [
            'path' => $path,
            'url'  => ($this->config['domain'] ?? '') . $path
        ];
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return bool
     */
    public function delete(string $filePath): bool
    {
        // 文件所在目录
        $realPath = public_path() . $filePath;
        return $realPath === false || unlink($realPath);
    }

    /**
     * 缩放及压缩图片
     * @param string $filePath
     * @return bool
     */
    private function resizeAndCompressImage($filePath): bool
    {
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            throw new \RuntimeException("文件不存在: " . $filePath);
        }

        // 获取图片信息
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            throw new \RuntimeException("无法读取图片信息: " . $filePath);
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // 检查是否需要缩放 (宽度 > 1080)
        $newWidth = $width;
        $newHeight = $height;
        $needsResize = $width > 1080;

        if ($needsResize) {
            $newWidth = 1080;
            $newHeight = (int)round($height * (1080 / $width));
        }

        // 根据 MIME 类型创建图像资源
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                throw new \RuntimeException("不支持的图片格式: " . $mimeType);
        }

        if ($image === false) {
            throw new \RuntimeException("无法创建图像资源: " . $filePath);
        }

        // 仅当需要缩放时创建新图像
        if ($needsResize || $mimeType == 'image/png') {
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // 处理非JPG格式的透明度
            if ($mimeType !== 'image/jpeg') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // 执行缩放
            imagecopyresampled(
                $newImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $width, $height
            );

            // 替换原始图像资源
            imagedestroy($image);
            $image = $newImage;
        }

        // 保存处理后的图像
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                // JPEG 质量压缩 (质量范围 0-100)
                $result = imagejpeg($image, $filePath, 85);
                break;
            case 'image/png':
                // PNG 压缩级别 (0-9)
                $result = imagepng($image, $filePath, 8);
                break;
            case 'image/gif':
                // GIF 不压缩
                $result = imagegif($image, $filePath);
                break;
            case 'image/webp':
                // WEBP 不压缩 (质量100)
                $result = imagewebp($image, $filePath, 85);
                break;
        }

        // 清理资源
        imagedestroy($image);

        if (!$result) {
            throw new \RuntimeException("保存图片失败: " . $filePath);
        }

        return true;
    }
}
