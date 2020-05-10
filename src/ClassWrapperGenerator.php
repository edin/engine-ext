<?php

namespace EngineExt;

final class ClassWrapperGenerator
{
    private ClassDeclaration $classType;

    public function __construct(ClassDeclaration $classType)
    {
        $this->classType = $classType;
    }

    public function generateFunctionEntry()
    {
        $functions = $this->classType->getFunctionDeclarations();
        $name      = $this->classType->getZendObjectTypeName();
        $prefix    = $this->classType->getSignaturePrefix();

        echo "static const zend_function_entry {$name}Functions[] =  {", "\n";
        foreach ($functions as $func) {
            echo "    PHP_ME({$name}, {$func->getName()}, {$prefix}_{$func->getName()}, ZEND_ACC_PUBLIC) /**/", "\n";
        }
        echo "    PHP_FE_END};\n\n";
    }

    public function generateArgInfo()
    {
        $functions = $this->classType->getFunctionDeclarations();
        $prefix    = $this->classType->getSignaturePrefix();

        foreach ($functions as $func) {
            $paramCount = $func->getParameterCount();
            $requiredParamCount = $func->getRequiredParameterCount();
            $functionName = $func->getName();

            //TODO: Get right return type
            echo "ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX({$prefix}_{$functionName}, {$requiredParamCount}, {$paramCount}, IS_VOID, 0)\n";
            foreach ($func->getParameters() as $param) {
                $dataType  = $param->getDataType();
                $paramName = $param->getName();
                $typeCheck = $param->getDataType()->getArgInfoConst();
                if ($dataType->isBuiltin()) {
                    echo "    ZEND_ARG_TYPE_INFO(0, {$paramName}, {$typeCheck}, 0)\n";
                } else {
                    //TODO: Get right object type
                    $typeName = "Object";
                    echo "    ZEND_ARG_OBJ_INFO(0, {$paramName}, {$typeName}, 0)";
                }
            }
            echo "ZEND_END_ARG_INFO()\n\n";
        }
    }

    public function generateClassEntry()
    {
        $typeName = $this->classType->getZendObjectTypeName();
        echo "zend_object_handlers       {$typeName}::ObjectHandlers;\n";
        echo "zend_class_entry*          {$typeName}::ClassEntry;\n";
    }

    public function generateFunctionEntryAsignment()
    {
        $typeName = $this->classType->getZendObjectTypeName();
        echo "const zend_function_entry* {$typeName}::FunctionEntry = &{$typeName}Functions[0];\n";
    }

    private function getDeclarationsAndArguments(FunctionDeclaration $function): object
    {
        $result = new \stdClass;
        $result->declarations = [];
        $result->arguments = [];

        foreach ($function->getParameters() as $param) {
            $paramType = $param->getDataType();
            $paramCType = $paramType->getCType();
            $defaultValue = $paramType->getCDefaultValue();
            $paramName = "_" . $paramType->getName();

            $result->declarations[] = "    {$paramCType} {$paramName} = $defaultValue;";
            if ($paramType->isString()) {
                $result->declarations[] = "    size_t {$paramName}_length = 0;";
            };

            if ($paramType->isString()) {
                $result->arguments[] = "std::string_view({$paramName}, {$paramName}_length)";
            } elseif ($paramType->isBuildin()) {
                $castTo = $paramType->getCastType();
                $result->arguments[] = "{$castTo}{$paramName}";
            } else {
                $zendTypeName = $paramType->getZendObjectTypeName();
                $result->arguments[] = "{$zendTypeName}::GetZendObject({$paramName})->data";
            }
        }
        return $result;
    }

    public function generateMethods()
    {
        $functions = $this->classType->getFunctionDeclarations();
        $zendTypeName = $this->classType->getZendObjectTypeName();
        $cppTypeName = $this->classType->getCppTypeName();

        foreach ($functions as $function) {
            $arguments = [];
            $paramCount = count($function->getParameters());
            $functionName = $function->getName();
            $decAndArgs = $this->getDeclarationsAndArguments($function);

            echo "PHP_METHOD($zendTypeName, $functionName)\n";
            echo "{\n";
            if (!$function->hasParameters()) {
                echo "    ZEND_PARSE_PARAMETERS_NONE();\n\n";
            } else {
                echo implode("\n", $decAndArgs->declarations);

                echo "\n\n";
                echo "    ZEND_PARSE_PARAMETERS_START($paramCount, $paramCount)\n";
                foreach ($function->getParameters() as $param) {
                    $paramType = $param->getDataType();
                    $parseParamMacro = $paramType->getParseParamMacro();
                    $paramZendTypeName = $paramType->getZendObjectTypeName();
                    $paramName = $param->getName();

                    if ($paramType->isString()) {
                        //TODO: Add length parsing
                        echo "        {$parseParamMacro}({$paramName})\n";
                    } elseif ($paramType->isBuiltin()) {
                        echo "        {$parseParamMacro}({$paramName})\n";
                    } else {
                        echo "        {$parseParamMacro}({$paramName}}, {$paramZendTypeName}::ClassEntry, 0, 0)\n";
                    }
                }
                echo "    ZEND_PARSE_PARAMETERS_END();\n\n";
            }

            $argumentList = implode(", ", $arguments);

            echo "    auto instance = $zendTypeName::GetZendObject(getThis());\n";

            if ($function->isConstructor()) {
                echo "    instance->data = {$cppTypeName}($argumentList);\n";
            } elseif ($function->hasReturnType()) {
                $returnType = $functionName->getReturnType();
                if ($returnType->isBuiltin()) {
                    // Return primitive type
                    $returnMacro = $returnType->getReturnMacro();
                    echo "    auto result = instance->data.{$functionName}({$argumentList});\n\n";
                    echo "    {$returnMacro}(result);\n";
                } elseif ($function->hasReturnThis()) {
                    // Method must be marked with @return(this) using doc block
                    // Return reference to the same object
                    echo "    instance->data.{$functionName}({$argumentList});\n";
                    echo "    RETURN_OBJ(getThis());\n";
                } else {
                    // Create an instance of the return type nad assign result of the function to the data member
                    $resultTypeName = $returnType->getZendObjectTypeName();
                    echo "    auto result = {$resultTypeName}::CreateNew();\n";
                    echo "    auto& object  = {$resultTypeName}::GetZendObject(result);\n";
                    echo "    object->data = instance->data.{$functionName}({$argumentList});\n";
                    echo "    RETURN_OBJ(result);\n";
                }
            } else {
                echo "    instance->data.$functionName($argumentList);\n";
            }
            echo "}\n\n";
        }
    }
}

// <?php

// include "php.stub.php";

// class TypeMapping {
//     public string $argumentType;
//     public string $returnType;
//     public string $parseParam;
//     public string $variableType;

//     public function __construct(string $argumentType, string $returnType, string $parseParam, string $variableType)
//     {
//         $this->argumentType = $argumentType;
//         $this->returnType = $returnType;
//         $this->parseParam = $parseParam;
//         $this->variableType = $variableType;
//     }
// }

// class ClassGenerator
// {
//     private ReflectionClass $type;
//     private array $typeMap;

//     public function __construct(string $className)
//     {
//         $this->type = new ReflectionClass($className);
//         $this->typeMap = [
//             'null'     => new TypeMapping("IS_NULL",     "RETURN_NULL", "_", "_"),
//             'int'      => new TypeMapping("IS_LONG",     "RETURN_LONG", "Z_PARAM_LONG", "zend_long"),
//             "float"    => new TypeMapping("IS_DOUBLE",   "RETURN_DOUBLE", "Z_PARAM_DOUBLE", "double"),
//             "string"   => new TypeMapping("IS_STRING",   "RETURN_STRING", "Z_PARAM_STRING", "char*"), // Parses char* & length
//             "bool"     => new TypeMapping("_IS_BOOL",    "RETURN_BOOL", "Z_PARAM_BOOL", "bool"),
//             "array"    => new TypeMapping("IS_ARRAY",    "RETURN_ARRAY", "Z_PARAM_ARRAY", "???"),
//             "object"   => new TypeMapping("IS_OBJECT",   "RETURN_OBJ", "Z_PARAM_OBJECT_OF_CLASS_EX", "zval*"),
//             "callable" => new TypeMapping("IS_CALLABLE", "RETURN_OBJ", "Z_PARAM_CALLABLE", "???"),
//             "void"     => new TypeMapping("IS_VOID",     "_",           "_", "_"),
//         ];
//     }

//     public function getTypeName(): string
//     {
//         return $this->type->getName();
//     }

//     public function getZendObjectTypeName(): string
//     {
//         return $this->type->getName() . "Object";
//     }

//     public function getTypeNameArg(): string
//     {
//         return $this->type->getName() . "Arg";
//     }

//     function generateFunctionEntry()
//     {
//         $methods = $this->type->getMethods();
//         $objectName = $this->getZendObjectTypeName();
//         $argPrefix = $this->getTypeNameArg();
//         echo "static const zend_function_entry {$objectName}Functions[] =  {", "\n";
//         foreach ($methods as $m) {
//             $methodName = $m->getName();
//             echo "    PHP_ME({$objectName}, {$methodName}, {$argPrefix}_{$methodName}, ZEND_ACC_PUBLIC) /**/", "\n";
//         }
//         echo "    PHP_FE_END};\n\n";
//     }

//     function generateArgInfo()
//     {
//         $methods = $this->type->getMethods();
//         $argPrefix = $this->getTypeNameArg();

//         foreach ($methods as $m)
//         {
//             $methodName = $m->getName();
//             echo "ZEND_BEGIN_ARG_INFO_EX({$argPrefix}_{$methodName}, 0, 0, 0)\n";
//             foreach($m->getParameters() as $p)
//             {
//                 $info = $this->getParamInfo($p);
//                 echo "    ZEND_ARG_TYPE_INFO(0, {$info->paramName}, {$info->mapping->argumentType}, 0)\n";
//             }
//             echo "ZEND_END_ARG_INFO()\n\n";
//         }
//     }

//     private function getParamInfo(ReflectionParameter $param)
//     {
//         $result  = new \stdClass;
//         $result->isBuiltin = $param->getType()->isBuiltin();
//         $result->paramType = $param->getType()->getName() ?? "unknown";
//         $result->paramName = $param->getName();
//         $result->mapping  = $this->typeMap[$result->paramType] ?? $this->typeMap['object'];
//         return $result;
//     }

//     private function getTypeInfo(ReflectionType $returnType)
//     {
//         $result  = new \stdClass;
//         $result->typeName = $returnType->getName() ?? "unknown";
//         $result->mapping  = $this->typeMap[$result->typeName] ?? $this->typeMap['object'];
//         return $result;
//     }

//     function generateMethods()
//     {
//         $methods = $this->type->getMethods();
//         $objectName = $this->getZendObjectTypeName();
//         $classTypeName = $this->getTypeName();
//         foreach ($methods as $m)
//         {
//             $paramCount = count($m->getParameters());
//             $argList = [];
//             $methodName = $m->getName();
//             echo "PHP_METHOD($objectName, $methodName)\n";
//             echo "{\n";
//             if ($paramCount == 0) {
//                 echo "    ZEND_PARSE_PARAMETERS_NONE();\n\n";
//             } else {
//                 foreach($m->getParameters() as $p) {
//                     $info = $this->getParamInfo($p);
//                     echo "    {$info->mapping->variableType} {$info->paramName};\n";
//                     if ($info->isBuiltin) {
//                         if ($info->paramType == "int") {
//                             $argList[] = "(int){$info->paramName}";
//                         } else {
//                             $argList[] = "{$info->paramName}";
//                         }
//                     } else {
//                         $argList[] = "{$info->paramType}Object::GetZendObject({$info->paramName})->data";
//                     }
//                 }

//                 echo "\n";

//                 echo "    ZEND_PARSE_PARAMETERS_START($paramCount, $paramCount)\n";
//                 foreach($m->getParameters() as $p) {
//                     $info = $this->getParamInfo($p);
//                     if ($info->isBuiltin) {
//                         echo "        {$info->mapping->parseParam}({$info->paramName})\n";
//                     } else {
//                         echo "        {$info->mapping->parseParam}({$info->paramName}, {$info->paramType}Object::ClassEntry, 0, 0)\n";
//                     }
//                 }
//                 echo "    ZEND_PARSE_PARAMETERS_END();\n\n";
//             }

//             $argListStr = implode(", ", $argList);

//             echo "    auto instance = $objectName::GetZendObject(getThis());\n";

//             if ($m->isConstructor()) {
//                 echo "    instance->data = {$classTypeName}($argListStr);\n";
//             } else {

//                 $returnType = $m->getReturnType();
//                 if ($returnType !== null)
//                 {
//                     if ($returnType->isBuiltin())
//                     {
//                         $typeInfo = $this->getTypeInfo($returnType);
//                         echo "    auto result = instance->data.$methodName($argListStr);\n\n";
//                         echo "    {$typeInfo->mapping->returnType}(result);\n";
//                     }
//                     else
//                     {
//                         $resultType = $returnType->getName() . "Object";

//                         echo "    auto result = {$resultType}::CreateNew();\n";
//                         echo "    {$resultType}::GetZendObject(result)->data = instance->data.{$methodName}($argListStr);\n";
//                         echo "    RETURN_OBJ(result);\n";
//                     }
//                 } else {
//                     echo "    instance->data.$methodName($argListStr);\n";
//                 }
//             }
//             echo "}\n\n";
//         }
//     }

//     public function generateClassEntry()
//     {
//         $typeName = $this->getZendObjectTypeName();

//         echo "// {$typeName}\n";
//         echo "zend_object_handlers       {$typeName}::ObjectHandlers;\n";
//         echo "zend_class_entry*          {$typeName}::ClassEntry;\n";
//     }

//     public function generateFunctionEntryAsignment()
//     {
//         $typeName = $this->getZendObjectTypeName();
//         echo "const zend_function_entry* {$typeName}::FunctionEntry = &{$typeName}Functions[0];\n";
//     }
// }

// echo <<<CODE
// #ifdef HAVE_CONFIG_H
// # include "config.h"
// #endif
// extern "C" {
// 	#include "php.h"
// 	#include "ext/standard/info.h"
// 	#include "php_cool.h"
// }
// #include "src/ZendObject.hpp"
// #include "src/Vector.hpp"
// #include "src/Color.hpp"

// using VectorObject = ZendObject<Vector>;
// using ColorObject  = ZendObject<Color>;
// \n
// CODE;


// $typeColor = new ClassGenerator(Color::class);
// $typeVector = new ClassGenerator(Vector::class);

// $typeColor->generateClassEntry();
// echo "\n";
// $typeVector->generateClassEntry();
// echo "\n";
// $typeColor->generateMethods();
// $typeColor->generateArgInfo();
// $typeColor->generateFunctionEntry();
// $typeColor->generateFunctionEntryAsignment();

// $typeVector->generateMethods();
// $typeVector->generateArgInfo();
// $typeVector->generateFunctionEntry();
// $typeVector->generateFunctionEntryAsignment();


// echo <<<CODE
// \n
// PHP_MINIT_FUNCTION(cool)
// {
// 	VectorObject::Register("Vector");
// 	ColorObject::Register("Color");
// 	return SUCCESS;
// }

// PHP_RINIT_FUNCTION(cool)
// {
// 	#if defined(ZTS) && defined(COMPILE_DL_COOL)
// 		ZEND_TSRMLS_CACHE_UPDATE();
// 	#endif
// 	return SUCCESS;
// }

// PHP_MINFO_FUNCTION(cool)
// {
// 	php_info_print_table_start();
// 	php_info_print_table_header(2, "cool support", "enabled");
// 	php_info_print_table_end();
// }
// \n
// zend_module_entry cool_module_entry = {
// 	STANDARD_MODULE_HEADER,
// 	"cool",                 /* Extension name */
// 	NULL,                   /* zend_function_entry */
// 	PHP_MINIT(cool),        /* PHP_MINIT - Module initialization */
// 	NULL,                   /* PHP_MSHUTDOWN - Module shutdown */
// 	PHP_RINIT(cool),        /* PHP_RINIT - Request initialization */
// 	NULL,                   /* PHP_RSHUTDOWN - Request shutdown */
// 	PHP_MINFO(cool),        /* PHP_MINFO - Module info */
// 	PHP_COOL_VERSION,       /* Version */
// 	STANDARD_MODULE_PROPERTIES
// };

// #ifdef COMPILE_DL_COOL
// # ifdef ZTS
// ZEND_TSRMLS_CACHE_DEFINE()
// # endif
// ZEND_GET_MODULE(cool)
// #endif\n
// CODE;
