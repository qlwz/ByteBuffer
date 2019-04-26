<?php
require_once __DIR__ . '/../vendor/autoload.php';

use qlwz\ByteBuffer\ByteBuffer;

$buf = new ByteBuffer();
$buf->put(0x02);
$buf->putShort(0xFEFE);
$buf->putInt(0x0102FEFE);
$buf->putLong(0x0F02FEFE0102FEFE);
$buf->putShort(0xFEFE, false);
$buf->putInt(0x0102FEFE, false);
$buf->putBytes($buf->data());
$buf->putToken($buf->data());

var_dump($buf->data());
