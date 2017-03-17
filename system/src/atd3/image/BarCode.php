<?php
namespace atd3\img;

class BarCode
{
    const TYPE_CODE128=0;
    const TYPE_CODE128A=1;
    const TYPE_CODE128B=2;
    const TYPE_CODE39=4;
    const TYPE_CODE25=5;
    const TYPE_CODABAR=6;

    private $type=BarCode::TYPE_CODE128;
    private $code=null;

    /// 编码表
    private static $CODE128A_TABLE = [' '=>212222,'!'=>222122,'"'=>222221,'#'=>121223,'$'=>121322,'%'=>131222,'&'=>122213,'\''=>122312,'('=>132212,')'=>221213,'*'=>221312,'+'=>231212,','=>112232,'-'=>122132,'.'=>122231,'/'=>113222,0=>123122,1=>123221,2=>223211,3=>221132,4=>221231,5=>213212,6=>223112,7=>312131,8=>311222,9=>321122,':'=>321221,';'=>312212,'<'=>322112,'='=>322211,'>'=>212123,'?'=>212321,'@'=>232121,'A'=>111323,'B'=>131123,'C'=>131321,'D'=>112313,'E'=>132113,'F'=>132311,'G'=>211313,'H'=>231113,'I'=>231311,'J'=>112133,'K'=>112331,'L'=>132131,'M'=>113123,'N'=>113321,'O'=>133121,'P'=>313121,'Q'=>211331,'R'=>231131,'S'=>213113,'T'=>213311,'U'=>213131,'V'=>311123,'W'=>311321,'X'=>331121,'Y'=>312113,'Z'=>312311,'['=>332111,'\\'=>314111,']'=>221411,'^'=>431111,'_'=>111224,'NUL'=>111422,'SOH'=>121124,'STX'=>121421,'ETX'=>141122,'EOT'=>141221,'ENQ'=>112214,'ACK'=>112412,'BEL'=>122114,'BS'=>122411,'HT'=>142112,'LF'=>142211,'VT'=>241211,'FF'=>221114,'CR'=>413111,'SO'=>241112,'SI'=>134111,'DLE'=>111242,'DC1'=>121142,'DC2'=>121241,'DC3'=>114212,'DC4'=>124112,'NAK'=>124211,'SYN'=>411212,'ETB'=>421112,'CAN'=>421211,'EM'=>212141,'SUB'=>214121,'ESC'=>412121,'FS'=>111143,'GS'=>111341,'RS'=>131141,'US'=>114113,'FNC 3'=>114311,'FNC 2'=>411113,'SHIFT'=>411311,'CODE C'=>113141,'CODE B'=>114131,'FNC 4'=>311141,'FNC 1'=>411131,'Start A'=>211412,'Start B'=>211214,'Start C'=>211232,'Stop'=>2331112];
    private static $CODE128B_TABLE = [' '=>212222,'!'=>222122,'"'=>222221,'#'=>121223,'$'=>121322,'%'=>131222,'&'=>122213,'\''=>122312,'('=>132212,')'=>221213,'*'=>221312,'+'=>231212,','=>112232,'-'=>122132,'.'=>122231,'/'=>113222,0=>123122,1=>123221,2=>223211,3=>221132,4=>221231,5=>213212,6=>223112,7=>312131,8=>311222,9=>321122,':'=>321221,';'=>312212,'<'=>322112,'='=>322211,'>'=>212123,'?'=>212321,'@'=>232121,'A'=>111323,'B'=>131123,'C'=>131321,'D'=>112313,'E'=>132113,'F'=>132311,'G'=>211313,'H'=>231113,'I'=>231311,'J'=>112133,'K'=>112331,'L'=>132131,'M'=>113123,'N'=>113321,'O'=>133121,'P'=>313121,'Q'=>211331,'R'=>231131,'S'=>213113,'T'=>213311,'U'=>213131,'V'=>311123,'W'=>311321,'X'=>331121,'Y'=>312113,'Z'=>312311,'['=>332111,'\\'=>314111,']'=>221411,'^'=>431111,'_'=>111224,'`'=>111422,'a'=>121124,'b'=>121421,'c'=>141122,'d'=>141221,'e'=>112214,'f'=>112412,'g'=>122114,'h'=>122411,'i'=>142112,'j'=>142211,'k'=>241211,'l'=>221114,'m'=>413111,'n'=>241112,'o'=>134111,'p'=>111242,'q'=>121142,'r'=>121241,'s'=>114212,'t'=>124112,'u'=>124211,'v'=>411212,'w'=>421112,'x'=>421211,'y'=>212141,'z'=>214121,'{'=>412121,'|'=>111143,'}'=>111341,'~'=>131141,'DEL'=>114113,'FNC 3'=>114311,'FNC 2'=>411113,'SHIFT'=>411311,'CODE C'=>113141,'FNC 4'=>114131,'CODE A'=>311141,'FNC 1'=>411131,'Start A'=>211412,'Start B'=>211214,'Start C'=>211232,'Stop'=>2331112];
    
    private $note=true;
    private $factor=1;
    private $size=20;
    private $text;

    public function __construct(int $type=BarCode::TYPE_CODE128, bool $note=true, int $size=20, int $factor=1)
    {
        $this->type=$type;
        $this->note=$note;
        $this->factor=$factor;
        $this->size;
    }

    public function generate(string $text)
    {
        $this->text=$text;
        switch ($this->type) {
            // 默认编码
            case BarCode::TYPE_CODE128:
            case BarCode::TYPE_CODE128B:
                $chksum = 104;
                $code_keys = array_keys(self::$CODE128B_TABLE);
                $code_values = array_flip($code_keys);
                for ($i = 1; $i <= strlen($text); $i++) {
                    $chr=  $text[$i-1];
                    $this->code .= self::$CODE128B_TABLE[$chr];
                    $chksum=($chksum + ($code_values[$chr] * $i));
                }
                $this->code .= self::$CODE128B_TABLE[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
                $this->code = self::$CODE128B_TABLE['Start B'] . $this->code .self::$CODE128B_TABLE['Stop'];
                break;
            case BarCode::TYPE_CODE128A:
                $chksum = 103;
                $text = strtoupper($text);
                $this->text=$text;
                $code_keys = array_keys(self::$CODE128A_TABLE);
                $code_values = array_flip($code_keys);
                for ($i = 1; $i <= strlen($text); $i++) {
                    $chr=  $text[$i-1];
                    $this->code .= self::$CODE128A_TABLE[$chr];
                    $chksum=($chksum + ($code_values[$chr] * $i));
                }
                $this->code .= self::$CODE128A_TABLE[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
                $this->code = self::$CODE128A_TABLE['Start A'] . $this->code .self::$CODE128A_TABLE['Stop'];
        }

        return $this;
    }

    public function display(string $path=null)
    {
        $code_length = 20;
        // 边框长度
        if ($this->note) {
            $text_height = 20;
        } else {
            $text_height = 0;
        }
        // 二维码长度
        for ($i=1; $i <= strlen($this->code); $i++) {
            $code_length += intval($this->code[$i-1]);
        }
        // 长宽
        $img_width = $code_length * $this->factor;
        $img_height = $this->size * $this->factor;
        // 构建基础图形
        $img = imagecreate($img_width, $img_height + $text_height);
        $black = imagecolorallocate($img, 0, 0, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        // 是否显示标记
        if ($this->note) {
            imagestring($img, 5, 31, $img_height, $this->text, $black);
        }
        // 绘图
        $location = 10;
        for ($position = 1 ; $position <= strlen($this->code); $position++) {
            $barsize = $location +  intval($this->code[$position-1]);
            imagefilledrectangle($img, $location*$this->factor, 0, $barsize*$this->factor, $img_height, ($position % 2 == 0 ? $white : $black));
            $location = $barsize;
        }
        // 显示图片
        imagepng($img);
        imagedestroy($img);
    }
}
