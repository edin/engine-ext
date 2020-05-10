<?php

namespace EngineExt;

use ReflectionClass;

final class ClassDeclaration
{
    private ReflectionClass $type;

    public function __construct(ReflectionClass $type)
    {
        $this->type = $type;
    }

    public function getFunctionDeclarations(): array
    {
        return array_map(fn ($e) => new FunctionDeclaration($e), $this->type->getMethods());
    }

    public function getName(): string
    {
        return $this->type->getName();
    }

    public function getZendObjectTypeName(): string
    {
        return $this->type->getName() . "Object";
    }

    public function getSignaturePrefix(): string
    {
        return $this->type->getName() . "Arg";
    }

    public function getCppTypeName(): string
    {
        return $this->type->getName();
    }
}
