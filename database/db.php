<?php

require_once __DIR__ . '/traits/ConnectionTrait.php';
require_once __DIR__ . '/traits/QueryTrait.php';
require_once __DIR__ . '/traits/SecurityTrait.php';
require_once __DIR__ . '/traits/FormatTrait.php';
require_once __DIR__ . '/traits/FlashTrait.php';
require_once __DIR__ . '/traits/TargetKasTrait.php';

class Koneksi
{
    use ConnectionTrait;
    use QueryTrait;
    use SecurityTrait;
    use FormatTrait;
    use FlashTrait;
    use TargetKasTrait;
}
