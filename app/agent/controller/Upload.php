<?php

namespace app\agent\controller;
use think\facade\Filesystem;
class Upload extends Base
{
    /**
     * 上传证书文件
     */
    public function pem()
    {
        $file = request()->file('file');
        $path = Filesystem::disk('public')->putFile('cert', $file, 'uniqid');
        $ext = strrchr($path, '.');
        if ($ext != '.pem') {
            return errorJson('只能上传.pem格式的文件');
        }

        return successJson($path);
    }
}
