<?php

namespace EngineExt;

use ReflectionParameter;

final class Parameter
{
    private ReflectionParameter $parameter;

    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function getName(): string
    {
        return $this->parameter->getName();
    }

    public function hasType(): bool
    {
        return $this->parameter->hasType();
    }

    public function isOptional(): bool
    {
        return $this->parameter->isOptional();
    }

    public function getDataType(): DataType
    {
        return new DataType($this->parameter->getType());
    }
}
