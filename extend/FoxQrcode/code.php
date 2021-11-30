<?php
namespace FoxQrcode;
use think\facade\Request;

class code
{
    public $out_dir = 'upload/qrcode';

    public function __construct($out_dir = '')
    {
        if($out_dir) {
            $this->out_dir = $out_dir;
        }
        require('phpqrcode/qrlib.php');
    }

    /**
     * 生成普通二维码
     * @param string $url 生成url地址
     * @param bool $outfile
     * @param int $size
     * @param string $evel
     * @return string $url
     */
    public function png($content, $filename = '', $size = 5, $evel = 'H')
    {
        if (!file_exists($this->out_dir)) {
            mkdir($this->out_dir, 0775, true);
        }

        if (!$filename) {
            $filename = uniqid();
        }
        $outfile = $this->out_dir . '/' . $filename . '.png';

        \QRcode::png($content, $outfile, $evel, $size, 2);

        return Request::instance()->domain().'/'.$outfile;
    }
}