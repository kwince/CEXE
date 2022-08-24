<?php
namespace Cexe;

require __DIR__ . '/vendor/autoload.php';

use \Cexe\ProjDataObjs\KeyFile;

$kf = new \Cexe\ProjDataObjs\KeyFile( "/home/gearond/key.bin", "/home/gearond/key.bin.hash", TRUE );
