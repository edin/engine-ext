<?php

namespace EngineExt;

use ReflectionFunctionAbstract;
use ReflectionMethod;

final class FunctionDeclaration
{
    private ReflectionFunctionAbstract $function;

    public function __construct(ReflectionFunctionAbstract $function)
    {
        $this->function = $function;
    }

    public function isConstructor(): bool
    {
        if ($this->function instanceof ReflectionMethod) {
            return $this->function->isConstructor();
        }
        return false;
    }

    public function getName(): string
    {
        return $this->function->getName();
    }

    public function hasReturnThis(): bool
    {
        $comment = $this->function->getDocComment();
        return strpos($comment, "@return(this)") !== false;
    }

    public function hasParameters(): bool
    {
        return $this->getParameterCount() > 0;
    }

    public function hasReturnType(): bool
    {
        return $this->function->hasReturnType();
    }

    public function getParameters(): array
    {
        return array_map(fn ($e) => new Parameter($e), $this->function->getParameters());
    }

    public function getReturnType(): DataType
    {
        return new DataType($this->function->getReturnType());
    }

    public function getParameterCount(): int
    {
        return count($this->function->getParameters());
    }

    public function getRequiredParameterCount(): int
    {
        $required = 0;
        foreach ($this->function->getParameters() as $p) {
            if (!$p->isOptional()) {
                $required++;
            }
        }
        return $required;
    }
}
