<?php

namespace qlwz\ByteBuffer;

use OutOfRangeException;

trait Read
{
    /**
     * 读取byte
     *
     * @return int
     */
    public function get()
    {
        $result = $this->getIndex($this->position);
        $this->position += 1;
        return $result;
    }

    /**
     * 在index处读取byte
     *
     * @param int $index
     * @return int
     */
    public function getIndex($index)
    {
        $this->assertOffsetAndLength($index, 1);
        return ord($this->buffer[$index]);
    }

    /**
     * 读取short
     *
     * @param bool $isBigEndian
     * @return int
     */
    public function getShort($isBigEndian = true)
    {
        $result = $this->getShortIndex($this->position, $isBigEndian);
        $this->position += 2;
        return $result;
    }

    /**
     * 在index处读取short
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return int
     */
    public function getShortIndex($index, $isBigEndian = true)
    {
        return $this->readInt($index, 2, $isBigEndian);
    }

    /**
     * 读取int
     *
     * @param bool $isBigEndian
     * @return int
     */
    public function getInt($isBigEndian = true)
    {
        $result = $this->getIntIndex($this->position, $isBigEndian);
        $this->position += 4;
        return $result;
    }

    /**
     * 在index处读取int
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return int
     */
    public function getIntIndex($index, $isBigEndian = true)
    {
        return $this->readInt($index, 4, $isBigEndian);
    }

    /**
     * 读取long
     *
     * @param bool $isBigEndian
     * @return int
     */
    public function getLong($isBigEndian = true)
    {
        $result = $this->getLongIndex($this->position, $isBigEndian);
        $this->position += 8;
        return $result;
    }

    /**
     * 在index处读取long
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return int
     */
    public function getLongIndex($index, $isBigEndian = true)
    {
        return $this->readInt($index, 8, $isBigEndian);
    }

    /**
     * 读取float
     *
     * @param bool $isBigEndian
     * @return float
     */
    public function getFloat($isBigEndian = true)
    {
        $result = $this->getFloatIndex($this->position, $isBigEndian);
        $this->position += 4;
        return $result;
    }

    /**
     * 在index处读取float
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return float
     */
    public function getFloatIndex($index, $isBigEndian = true)
    {
        $result    = $this->readInt($index, 4, $isBigEndian);
        $inthelper = pack('V', $result);
        $v         = unpack('f', $inthelper);
        return $v[1];
    }

    /**
     * 读取double
     *
     * @param bool $isBigEndian
     * @return float
     */
    public function getDouble($isBigEndian = true)
    {
        $result = $this->getDoubleIndex($this->position, $isBigEndian);
        $this->position += 8;
        return $result;
    }

    /**
     * 在index处读取double
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return float
     */
    public function getDoubleIndex($index, $isBigEndian = true)
    {
        $i         = $this->readInt($index, 4, $isBigEndian);
        $i2        = $this->readInt($index + 4, 4, $isBigEndian);
        $inthelper = pack('VV', $i, $i2);
        $v         = unpack('d', $inthelper);
        return $v[1];
    }

    /**
     * 读取N个字节
     *
     * @param int $count
     * @return string
     */
    public function getBytes($count)
    {
        $str = $this->getBytesIndex($this->position, $count);
        $this->position += $count;
        return $str;
    }

    /**
     * 在index处读取N个字节
     *
     * @param int $index
     * @param int $count
     * @return string
     */
    public function getBytesIndex($index, $count)
    {
        $this->assertOffsetAndLength($index, $count);
        $str = substr($this->buffer, $index, $count);
        return $str;
    }

    /**
     * 读取一个Toekn
     *
     * @param bool $isBigEndian
     * @return string
     */
    public function getToken($isBigEndian = true)
    {
        $result = $this->getTokenIndex($this->position, $isBigEndian);
        $this->position += 2 + strlen($result);
        return $result;
    }

    /**
     * 在index处读取一个Toekn
     *
     * @param int $index
     * @param bool $isBigEndian
     * @return string
     */
    public function getTokenIndex($index, $isBigEndian = true)
    {
        $len = $this->getShortIndex($index, $isBigEndian);
        return $this->getBytesIndex($index + 2, $len);
    }

    /**
     * 读取一个Toekn
     *
     * @return string
     */
    public function getTokenByte()
    {
        $result = $this->getTokenByteIndex($this->position);
        $this->position += 1 + strlen($result);
        return $result;
    }

    /**
     * 在index处读取一个Toekn
     *
     * @param int $index
     * @return string
     */
    public function getTokenByteIndex($index)
    {
        $len = $this->getIndex($index);
        return $this->getBytesIndex($index + 1, $len);
    }

    /**
     * 读取一个数字
     *
     * @param int $offset
     * @param int $count
     * @param bool $isBigEndian
     * @return int
     */
    private function readInt($offset, $count, $isBigEndian = true)
    {
        $this->assertOffsetAndLength($offset, $count);
        $r = 0;
        if ($isBigEndian) {
            for ($i = 0; $i < $count; $i++) {
                $r |= ord($this->buffer[$offset + $count - 1 - $i]) << $i * 8;
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $r |= ord($this->buffer[$offset + $i]) << $i * 8;
            }
        }
        return $r;
    }

    /**
     * @param $offset
     * @param $length
     */
    private function assertOffsetAndLength($offset, $length)
    {
        $len = strlen($this->buffer);
        if ($offset < 0 || $offset >= $len || $offset + $length > $len) {
            throw new OutOfRangeException(sprintf('offset: %d, length: %d, buffer; %d', $offset, $length, $len));
        }
    }
}
