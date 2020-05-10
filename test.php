<?php

use EngineExt\ExtensionGenerator;

require "./vendor/autoload.php";

$gen = new ExtensionGenerator("sample/Extension.Interface.php");

foreach ($gen->getTypeDeclarations() as $type) {
    echo $type->getName(), "\n";
    foreach ($type->getFunctionDeclarations() as $func) {
        echo "  => ", $func->getName(), "\n";
    }
}
