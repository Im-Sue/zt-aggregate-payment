<?php

namespace app\agent\controller;

use think\facade\Db;

class Qrcode extends Base
{
    /**
     * 获取列表
     */
    public function getList()
    {
        $shop_id = input('shop_id', 0, 'intval');
        $page = input('page', 1, 'intval');
        $pagesize = input('pagesize', 10, 'intval');
        $page = max(1, $page);
        $pagesize = max(1, $pagesize);

        // 查询列表
        $where = [
            ['agent_id', '=', self::$agent['id']],
            ['shop_id', '=', $shop_id]
        ];
        $list = Db::name('qrcode')
            ->where($where)
            ->field('id,shop_id,title,speaker_status,FROM_UNIXTIME(add_time, \'%Y-%m-%d %H:%i\') as add_time')
            ->page($page, $pagesize)
            ->select()->each(function ($item) {
                $item['qrcode'] = mediaUrl('/upload/qrcode/qrcode_' . $item['shop_id'] . '_' . $item['id'] . '.png');
                return $item;
            });
        $count = Db::name('qrcode')
            ->where($where)
            ->count();

        return successJson([
            'count' => $count,
            'list' => $list
        ]);
    }

    /**
     * 取单个资料
     */
    public function getInfo()
    {
        $id = input('id', 0, 'intval');

        $info = Db::name('qrcode')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->field('id,title,speaker_status,speaker_brand,speaker_config')
            ->find();
        if($info['speaker_config']) {
            $config = json_decode($info['speaker_config'], true);
            if($info['speaker_brand'] == 'pinsheng') {
                $info['speaker_devid'] = $config['devid'];
            }
            unset($info['speaker_config']);
        }

        if (!$info) {
            return errorJson('没有找到数据，请刷新页面重试');
        }

        return successJson($info);
    }

    /**
     * 更新或新增
     */
    public function saveInfo()
    {
        $id = input('id', 0, 'intval');
        $shop_id = input('shop_id', 0, 'intval');
        $title = input('title', '', 'trim');
        $speaker_status = input('speaker_status', '', 'trim');
        $speaker_brand = input('speaker_brand', '', 'trim');
        if (empty($title)) {
            return errorJson('名称不能为空');
        }
        $shop = Db::name('shop')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $shop_id]
            ])
            ->find();
        if (!$shop) {
            return errorJson('商户不存在，请刷新页面重试');
        }

        $data = [];
        $data['title'] = $title;
        $data['speaker_status'] = $speaker_status;
        $data['speaker_brand'] = $speaker_brand;
        if ($speaker_status) {
            $speaker = [];
            if ($speaker_brand == 'pinsheng') {
                $speaker['devid'] = input('speaker_devid', '', 'trim');
            }
            $data['speaker_config'] = json_encode($speaker);
        }

        // 更新或添加
        if ($id) {
            $res = Db::name('qrcode')
                ->where([
                    ['shop_id', '=', $shop_id],
                    ['id', '=', $id]
                ])
                ->update($data);
        } else {
            $data['agent_id'] = $shop['agent_id'];
            $data['shop_id'] = $shop_id;
            $data['add_time'] = time();
            $res = Db::name('qrcode')->insert($data);
            $id = Db::name('qrcode')->getLastInsID();
        }

        if ($res !== false) {
            // 重新生成二维码图片
            $dir = 'upload/qrcode';
            if(defined('__PUBLIC__')) {
                $dir = __PUBLIC__ . $dir;
            }
            $code = new \FoxQrcode\code($dir);
            $url = mobileUrl('/pay/qrcode/index', ['id' => $id]);
            $filename = 'qrcode_' . $shop_id . '_' . $id;
            $size = 10;
            $code->png($url, $filename, $size);
            return successJson('', '保存成功');
        } else {
            return errorJson('保存失败，请重试！');
        }

    }

    /**
     * 删除
     */
    public function del()
    {
        $id = input('id', 0, 'intval');
        $res = Db::name('qrcode')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->delete();
        if ($res !== false) {
            return successJson('', '删除成功');
        } else {
            return errorJson('删除失败，请重试！');
        }
    }

    /**
     * 测试音箱配置
     */
    public function speakerTest()
    {
        $brand = input('speaker_brand', '', 'trim');
        if($brand == 'pinsheng') {
            $devid = input('speaker_devid', '', 'trim');
            $setting = getSystemSetting('speaker');
            if(empty($setting['pinsheng_accessKeyId']) || empty($setting['pinsheng_accessSecret'])) {
                return errorJson('请先完善系统配置中的收款音箱配置');
            }
            $speaker = new \Speaker\pinsheng\sdk($setting['pinsheng_username'], $setting['pinsheng_password']);
            $result = $speaker->play($devid,'alipay', 0.01);
            if ($result === true) {
                return successJson('', '播放命令已发送');
            } else {
                return errorJson('命令发送失败：' . $result['message']);
            }
        }

        return errorJson('暂不支持');
    }

    /**
     * 下载二维码
     */
    public function qrcodeDownload()
    {
        $id = input('id', 0, 'intval');
        $shop_id = input('shop_id', 0, 'intval');
        $qrcode = Db::name('qrcode')
            ->where([
                ['agent_id', '=', self::$agent['id']],
                ['id', '=', $id]
            ])
            ->field('title')
            ->find();
        if(!$qrcode) {
            return errorJson('二维码不存在');
        }

        $filename = 'upload/qrcode/qrcode_' . $shop_id . '_' . $id . '.png';
        if (!file_exists($filename)) {
            return errorJson('图片不存在');
        }
        //设置头信息
        header('Content-Disposition:attachment;filename=' . $qrcode['title'] . '.png');
        header('Content-Length:' . filesize($filename));
        //读取文件并写入到输出缓冲
        echo file_get_contents($filename);
        exit;
    }
}
