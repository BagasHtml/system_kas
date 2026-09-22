<?php

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/traits/ConnectionTrait.php';
require_once __DIR__ . '/traits/QueryTrait.php';
require_once __DIR__ . '/traits/SecurityTrait.php';
require_once __DIR__ . '/traits/FormatTrait.php';
require_once __DIR__ . '/traits/FlashTrait.php';
require_once __DIR__ . '/traits/TargetKasTrait.php';
<<<<<<< HEAD
require_once __DIR__ . '/traits/BelanjaTrait.php';
=======
require_once __DIR__ . '/traits/UploadTrait.php';
require_once __DIR__ . '/traits/SettingTrait.php';
>>>>>>> d67dedf (update layout and added new system for manage admin dashboard and added fix more bugs and update layout and added readme)

class Koneksi
{
    use ConnectionTrait;
    use QueryTrait;
    use SecurityTrait;
    use FormatTrait;
    use FlashTrait;
    use TargetKasTrait;
<<<<<<< HEAD
    use BelanjaTrait;
=======
    use UploadTrait;
    use SettingTrait;
>>>>>>> d67dedf (update layout and added new system for manage admin dashboard and added fix more bugs and update layout and added readme)
}

require_once __DIR__ . '/migrate_v2.php';
require_once __DIR__ . '/migrate_belanja.php';
