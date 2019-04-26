<?php

namespace qlwz\ByteBuffer;

use InvalidArgumentException;

trait Write
{
    /**
     * 写入1字节
     *
     * @param int $value
     * @return $this
     */
    public function put($value)
    {
        $this->putOffset($this->position, $value);
        $this->position += 1;
        return $this;
    }

    /**
     * 在offset处写入1字节
     *
     * @param int $offset
     * @param int $value
     * @return $this
     */
    public function putOffset($offset, $value)
    {
        self::validateValue(0, 255, $value, 'byte');
        $this->buffer[$offset] = chr($value);
        return $this;
    }

    /**
     * 写入short
     *
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putShort($value, $isBigEndian = true)
    {
        $this->putShortOffset($this->position, $value, $isBigEndian);
        $this->position += 2;
        return $this;
    }

    /**
     * 在offset处写入short
     *
     * @param int $offset
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putShortOffset($offset, $value, $isBigEndian = true)
    {
        self::validateValue(0, 65535, $value, 'short');
        $this->writeInt($offset, 2, $value, $isBigEndian);
        return $this;
    }

    /**
     * 写入int
     *
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putInt($value, $isBigEndian = true)
    {
        $this->putIntOffset($this->position, $value, $isBigEndian);
        $this->position += 4;
        return $this;
    }

    /**
     * 在offset处写入int
     *
     * @param $offset
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putIntOffset($offset, $value, $isBigEndian = true)
    {
        // NOTE: We can't put big integer value. this is PHP limitation.
        // 4294967295 = (1 << 32) -1 = Maximum unsigned 32-bin int
        self::validateValue(0, 4294967295, $value, 'uint', ' php has big numbers limitation. check your PHP_INT_MAX');
        $this->writeInt($offset, 4, $value, $isBigEndian);
        return $this;
    }

    /**
     * 写入long
     *
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putLong($value, $isBigEndian = true)
    {
        $this->putLongOffset($this->position, $value, $isBigEndian);
        $this->position += 8;
        return $this;
    }

    /**
     * 在offset处写入long
     *
     * @param int $offset
     * @param int $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putLongOffset($offset, $value, $isBigEndian = true)
    {
        // NOTE: We can't put big integer value. this is PHP limitation.
        self::validateValue(0, PHP_INT_MAX, $value, 'long', ' php has big numbers limitation. check your PHP_INT_MAX');
        $this->writeInt($offset, 8, $value, $isBigEndian);
        return $this;
    }

    /**
     * 写入double
     * @param float $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putDouble($value, $isBigEndian = true)
    {
        $this->putDoubleOffset($this->position, $value, $isBigEndian);
        $this->position += 8;
        return $this;
    }

    /**
     * 在offset处写入double
     * @param int $offset
     * @param float $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putDoubleOffset($offset, $value, $isBigEndian = true)
    {
        $this->assertOffsetAndLength($offset, 8);
        $floathelper = pack('d', $value);
        $v           = unpack('V*', $floathelper);
        $this->writeInt($offset, 4, $v[1], $isBigEndian);
        $this->writeInt($offset + 4, 4, $v[2], $isBigEndian);
        return $this;
    }

    /**
     * 写入float
     * @param int $offset
     * @param float $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putFloat($offset, $value, $isBigEndian = true)
    {
        $this->putFloatOffset($offset, $value, $isBigEndian);
        $this->position += 4;
        return $this;
    }

    /**
     * 在offset处写入float
     * @param int $offset
     * @param float $value
     * @param bool $isBigEndian
     * @return $this
     */
    public function putFloatOffset($offset, $value, $isBigEndian = true)
    {
        $this->assertOffsetAndLength($offset, 4);
        $floathelper = pack('f', $value);
        $v           = unpack('V', $floathelper);
        $this->writeInt($offset, 4, $v[1], $isBigEndian);
        return $this;
    }

    /**
     * 写入字节
     *
     * @param string $value
     * @return $this
     */
    public function putBytes($value)
    {
        $this->putBytesOffset($this->position, $value);
        $this->position += strlen($value);
        return $this;
    }

    /**
     * 在offset处写入字节
     *
     * @param int $offset
     * @param string $value
     * @return $this
     */
    public function putBytesOffset($offset, $value)
    {
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $this->buffer[$offset + $i] = $value[$i];
        }
        return $this;
    }

    /**
     * 写入Token，长度为双字节
     *
     * @param string $token
     * @param bool $isBigEndian
     * @return $this
     */
    public function putToken($token, $isBigEndian = true)
    {
        $this->putTokenOffset($this->position, $token, $isBigEndian);
        $this->position += 2 + strlen($token);
        return $this;
    }

    /**
     * 在offset处写入Token，长度为双字节
     *
     * @param int $offset
     * @param string $token
     * @param bool $isBigEndian
     * @return $this
     */
    public function putTokenOffset($offset, $token, $isBigEndian = true)
    {
        $this->putShortOffset($offset, strlen($token), $isBigEndian);
        $this->putBytesOffset($offset + 2, $token);
        return $this;
    }

    /**
     * 写入Token，长度为单字节
     *
     * @param string $token
     * @return $this
     */
    public function putTokenByte($token)
    {
        $this->putTokenByteOffset($this->position, $token);
        $this->position += 1 + strlen($token);
        return $this;
    }

    /**
     * 在offset处写入Token，长度为单字节
     *
     * @param int $offset
     * @param string $token
     * @return $this
     */
    public function putTokenByteOffset($offset, $token)
    {
        $this->putOffset($offset, strlen($token));
        $this->putBytesOffset($offset + 1, $token);
        return $this;
    }

    /**
     * 写入一个数字
     *
     * @param int $offset
     * @param int $count byte length
     * @param int $data actual values
     * @param bool $isBigEndian
     */
    public function writeInt($offset, $count, $data, $isBigEndian = true)
    {
        if ($isBigEndian) {
            for ($i = 0; $i < $count; $i++) {
                $this->buffer[$offset + $count - 1 - $i] = chr($data >> $i * 8);
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $this->buffer[$offset + $i] = chr($data >> $i * 8);
            }
        }
    }

    private static function validateValue($min, $max, $value, $type, $additional_notes = '')
    {
        if (!($min <= $value && $value <= $max)) {
            throw new InvalidArgumentException(sprintf('bad number %s for type %s.%s', $value, $type, $additional_notes));
        }
    }
}
