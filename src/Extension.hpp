#pragma once

enum class AccessModifier
{
    Private,
    Protected,
    Public
};

enum class ClassModifier
{
    None,
    Abstract,
    Final
};

enum class DataType
{
    Void,
    Int,
    Float,
    Bool,
    Array,
    String,
    Object
};

enum class Pass
{
    ByValue,
    ByReference
};

class Type
{
private:
    Pass passBy = Pass::ByValue;
    DataType dataType = DataType::Void;
    const char *name = nullptr;
    const char *typeName = nullptr;

public:
    Type() {}
    Type(const char *name) : name(name) {}

    Type &byRef()
    {
        this->passBy = Pass::ByReference;
        return *this;
    }
};

class MethodSignature
{
public:
    Type returnType;
    //List<Type> parameters;
};

template <typename TClass>
class TypeInfo
{
public:
    ClassModifier classModifier = ClassModifier::None;

    template <typename TMethod>
    void addMethod(TMethod method, const char *name, const MethodSignature &signature)
    {
    }

    void setClassModifier(ClassModifier modifier)
    {
        this->classModifier = modifier;
    }
};

class Extension
{
private:
    const char *name;
    const char *rootNamespace = "";

public:
    Extension(const char *name, const char *rootNamespace) : name(name),
                                                             rootNamespace(rootNamespace)
    {
    }

    template <typename T, typename TMethod>
    void addMethod(TMethod method)
    {
    }

    template <typename T, typename TMethod>
    void addType(const char *name, TMethod method)
    {
    }
};
