<?php
namespace atd3\image;

/**
* 验证码生成器
*
*/

class VeCode
{
    const USE_ZH=0;
    const USE_EN=1;
    
    const IMG_PNG=0;
    const IMG_JPG=1;

    private $leng=4;
    private $verifycode=null;
    private $fontsize=18;
    private $fontfile=null;
    private $width;
    private $heigth;
    
    public function __construct(string $font, int $fontsize=18, int $leng=4)
    {
        $this->leng=$leng;
        $this->fontsize=$fontsize;
        $this->fontfile=$font;
    }

    public function generate(string $code=null)
    {
        if (is_null($code)) {
            for ($i = 0; $i < $this->leng; $i++) {
                // 英文~数字
                $this->verifycode .= rand()%2?chr(mt_rand(49, 57)):chr(mt_rand(65, 90));
            }
        } else {
            $this->verifycode=$code;
        }
        $bbox=imagettfbbox($this->fontsize, 0, $this->fontfile, $this->verifycode);
        $xlen=[$bbox[0],$bbox[2],$bbox[4],$bbox[6]];
        $ylen=[$bbox[1],$bbox[3],$bbox[5],$bbox[7]];
        sort($xlen);
        sort($ylen);
        $this->width=$xlen[3]-$xlen[0];
        $this->heigth=$ylen[3]-$ylen[0];
        return $this->verifycode;
    }

    public function code()
    {
        return $this->verifycode;
    }

    public function display(int $type,string $file=null)
    {
        $max_width=$this->width+$this->fontsize;
        $max_height= $this->heigth+$this->fontsize;
        $img = imagecreate($max_width, $max_height);
        $bgColor =  imagecolorallocate($img, mt_rand(245, 255), mt_rand(245, 255), mt_rand(245, 255)) ;
        for ($i = 0; $i < $this->leng; $i++) {
            $ic=$i * $this->fontsize + 10;
            $fz=$this->fontsize * 1.5;
            $x = mt_rand($ic -5, $ic +5);
            $y = mt_rand($fz-5, $fz+5);
            $text_color = imagecolorallocate($img, mt_rand(30, 180), mt_rand(10, 100), mt_rand(40, 250));
            imagettftext($img, $this->fontsize, 0, $x, $y, $text_color, $this->fontfile, $this->verifycode[$i]);
            imagearc($img,  mt_rand(0, 80) ,  mt_rand(30, 80), mt_rand(30, 180),  mt_rand(40, 180),  mt_rand($max_width, 180),  mt_rand($max_height, 180), $text_color);
        }
        for ($j = 0; $j < 100; $j++) {
            $pixColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 200), mt_rand(40, 250));
            $x = mt_rand(0, $max_width);
            $y = mt_rand(0, $max_height);
            imagesetpixel($img, $x, $y, $pixColor);
        }
        if ($type==IMG_PNG) {
            imagepng($img);
        } else {
            imagejpeg($img);
        }
        imagedestroy($img);
    }
}
