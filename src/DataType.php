<?php

namespace EngineExt;

use ReflectionNamedType;

final class DataType
{
    private ReflectionNamedType $type;

    public function __construct(ReflectionNamedType $type)
    {
        $this->type = $type;
    }

    public function getTypeName(): string
    {
        return $this->type->getName();
    }

    public function isBuiltin(): bool
    {
        return $this->type->isBuiltin();
    }

    public function isString(): bool
    {
        return $this->getTypeName() == "string";
    }

    public function isInt(): bool
    {
        return $this->getTypeName() == "int";
    }

    public function getParseParamMacro(): string
    {
        switch ($this->getTypeName()) {
            case "bool": return "Z_PARAM_BOOL";
            case "int": return "Z_PARAM_LONG";
            case "float": return "Z_PARAM_DOUBLE";
            case "string": return "Z_PARAM_DOUBLE";
            case "array": return "Z_PARAM_ARRAY";
            case "object": return "Z_PARAM_OBJECT_OF_CLASS_EX";
            case "callable": return "Z_PARAM_CALLABLE";
        }
    }

    public function getReturnMacro(): string
    {
        switch ($this->getTypeName()) {
            case "null": return "RETURN_NULL";
            case "bool": return "RETURN_BOOL";
            case "int": return "RETURN_LONG";
            case "float": return "RETURN_DOUBLE";
            case "string": return "RETURN_STRING";
            case "array": return "RETURN_ARRAY";
            case "callable": return "RETURN_OBJ";
            case "object": return "RETURN_OBJ";
        }
    }

    public function getArgInfoConst(): string
    {
        switch ($this->getTypeName()) {
            case "null":   return "IS_NULL";
            case "bool":   return "_IS_BOOL";
            case "int":    return "IS_LONG";
            case "float":  return "IS_DOUBLE";
            case "string": return "IS_STRING";
            case "array":  return "IS_ARRAY";
            case "object": return "IS_OBJECT";
            case "callable": return "IS_CALLABLE";
            case "void": return "IS_VOID";
        }
    }

    public function getCType(): string
    {
        switch ($this->getTypeName()) {
            case "bool": return "bool";
            case "int": return "zend_long";
            case "float": return "double";
            case "string": return "char*";
            case "array": return "zval*";
            case "object": return "zval*";
            case "callable": return "zval*";
        }
    }

    public function getCDefaltValue(): string
    {
        switch ($this->getTypeName()) {
            case "bool": return "false";
            case "int": return "0";
            case "float": return "0";
            case "string": return "NULL";
            case "array": return "NULL";
            case "object": return "NULL";
            case "callable": return "NULL";
        }
    }

    public function getCastType(): string
    {
        switch ($this->getTypeName()) {
            case "bool": return "";
            case "int": return "(int)";
            case "float": return "";
            case "string": return "";
            case "array": return "";
            case "object": return "";
        }
        return "";
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
