<?php

namespace EngineExt;

use ReflectionClass;

class ExtensionGenerator
{
    private array $types = [];
    private array $typeDeclarations = [];
    private string $fileName = "";
    private string $namespace = "";

    public function __construct(string $fileName)
    {
        include_once $fileName;

        $this->fileName = $fileName;
        $file = file_get_contents($fileName);
        $result = \preg_match_all("/interface(\s+)(\w+)/", $file, $matches);
        $resultNs = \preg_match("/namespace(\s+)([\\\\\\w]+);/", $file, $ns);

        $this->namespace = $ns[2] ?? "";

        $this->types = $matches[2];
        if ($result) {
            $this->types = $matches[2];
            $this->types = array_map(fn ($x) => $this->namespace . "\\" . $x, $this->types);
            $this->typeDeclarations = array_map(fn ($x) => new ClassDeclaration(new ReflectionClass($x)), $this->types);
        }
    }

    public function getTypeDeclarations(): array
    {
        return $this->typeDeclarations;
    }
}
