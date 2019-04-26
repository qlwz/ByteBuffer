<?php

namespace qlwz\ByteBuffer;

use InvalidArgumentException;

class ByteBuffer
{
    use Read;
    use Write;

    /**
     * @var string;
     */
    private $buffer;

    /**
     * @var int;
     */
    private $position;

    /**
     * @var bool 是否小端模式
     */
    private static $isLittleEndian = null;

    /**
     * @param string|int $value
     */
    public function __construct($value = 0)
    {
        switch (gettype($value)) {
            case 'integer':
                $this->position = 0;
                $this->buffer   = str_repeat("\0", intval($value));
                break;
            case 'string':
                $this->position = 0;
                $this->buffer   = $value;
                break;
            default:
                throw new InvalidArgumentException('Constructor argument must be a binary string or integer.');
        }
    }

    public function data()
    {
        return substr($this->buffer, 0, $this->position);
    }

    /**
     * 获取用字节表示的流长度
     *
     * @return string
     */
    public function getLength()
    {
        return strlen($this->buffer);
    }

    /**
     * 获取当前流中的位置
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * 设置当前流中的位置
     *
     * @param int $position 流位置
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * 获取可用字节个数
     *
     * @return int
     */
    public function getRemaining()
    {
        return $this->getLength() - $this->getPosition();
    }

    /**
     * 是否还有可用字节
     *
     * @return int
     */
    public function hasRemaining()
    {
        return $this->getRemaining() > 0;
    }

    /**
     * 是否小端模式
     * @return bool
     */
    public static function isLittleEndian()
    {
        if (self::$isLittleEndian === null) {
            self::$isLittleEndian = unpack('S', "\x01\x00")[1] === 1;
        }
        return self::$isLittleEndian;
    }

    /**
     * 字符串转十六进制
     * @param string $str
     * @return string
     */
    public static function strToHex($str)
    {
        $hex = '';
        for ($i = 0; $i < strlen($str) - 1; $i += 2) {
            $hex .= chr(hexdec($str[$i] . $str[$i + 1]));
        }
        return $hex;
    }

    /**
     * 十六进制转字符串
     *
     * @param string $hex
     * @return string
     */
    public static function hexToStr($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i++) {
            $ord     = ord($hex[$i]);
            $hexCode = dechex($ord);
            $str .= substr('0' . $hexCode, -2);
        }
        return strtoupper($str);
    }
}
