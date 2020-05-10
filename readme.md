# Basic concept

- Define extension interface by using php interfaces
- Generate wrapper for C++ Objects
- Keep C++ Objects clean

## Something like

1. Define interface

- Define global functions and constants using special "Functions" interface
- Define Vector interface and use doc-comments to mark that it should map to final class

```php
<?php

/** @functions */
interface Functions
{
    public const A = 100;
    public const B = 200;

    public function function1(float $value): Color;
    public function function2(float $value): Color;
}

/** @final @class */
interface Vector
{
    public function __construct(float $x, float $y, float $z);
    public function getX(): float;
    public function getY(): float;
    public function getZ(): float;
    public function setX(float $value);
    public function setY(float $value);
    public function setZ(float $value);
    public function add(Vector $vector): Vector;
    public function scale(float $value): Vector;
}
```

2. Run generator

```
$ .......... Boo! Not completed yet ..........
```

3. Generate calls and argument info

```cpp

#include "src/ZendObject.hpp"
#include "src/Vector.hpp"

using VectorObject = ZendObject<Vector>;

// VectorObject
zend_object_handlers       VectorObject::ObjectHandlers;
zend_class_entry*          VectorObject::ClassEntry;

// Vector Methods
PHP_METHOD(VectorObject, __construct)
{
    double x = 0.0;
    double y = 0.0;
    double z = 0.0;

    ZEND_PARSE_PARAMETERS_START(3, 3)
        Z_PARAM_DOUBLE(x)
        Z_PARAM_DOUBLE(y)
        Z_PARAM_DOUBLE(z)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    instance->data = Vector(x, y, z);
}

PHP_METHOD(VectorObject, getX)
{
    ZEND_PARSE_PARAMETERS_NONE();

    auto instance = VectorObject::GetZendObject(getThis());
    auto result = instance->data.getX();

    RETURN_DOUBLE(result);
}

PHP_METHOD(VectorObject, getY)
{
    ZEND_PARSE_PARAMETERS_NONE();

    auto instance = VectorObject::GetZendObject(getThis());
    auto result = instance->data.getY();

    RETURN_DOUBLE(result);
}

PHP_METHOD(VectorObject, getZ)
{
    ZEND_PARSE_PARAMETERS_NONE();

    auto instance = VectorObject::GetZendObject(getThis());
    auto result = instance->data.getZ();

    RETURN_DOUBLE(result);
}

PHP_METHOD(VectorObject, setX)
{
    double value;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_DOUBLE(value)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    instance->data.setX(value);
}

PHP_METHOD(VectorObject, setY)
{
    double value;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_DOUBLE(value)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    instance->data.setY(value);
}

PHP_METHOD(VectorObject, setZ)
{
    double value;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_DOUBLE(value)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    instance->data.setZ(value);
}

PHP_METHOD(VectorObject, add)
{
    zval* vector;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_OBJECT_OF_CLASS_EX(vector, VectorObject::ClassEntry, 0, 0)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    auto result = VectorObject::CreateNew();
    VectorObject::GetZendObject(result)->data = instance->data.add(VectorObject::GetZendObject(vector)->data);
    RETURN_OBJ(result);
}

PHP_METHOD(VectorObject, scale)
{
    double value;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_DOUBLE(value)
    ZEND_PARSE_PARAMETERS_END();

    auto instance = VectorObject::GetZendObject(getThis());
    auto result = VectorObject::CreateNew();
    auto vector = VectorObject::GetZendObject(result);
    vector->data = instance->data.scale(value);
    RETURN_OBJ(result);
}

// Vector Argument Info

ZEND_BEGIN_ARG_INFO_EX(VectorArg___construct, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, x, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_INFO(0, y, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_INFO(0, z, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_getX, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_getY, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_getZ, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_setX, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, value, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_setY, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, value, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_setZ, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, value, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_add, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, vector, IS_OBJECT, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(VectorArg_scale, 0, 0, 0)
    ZEND_ARG_TYPE_INFO(0, value, IS_DOUBLE, 0)
ZEND_END_ARG_INFO()

static const zend_function_entry VectorObjectFunctions[] =  {
    PHP_ME(VectorObject, __construct, VectorArg___construct, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, getX, VectorArg_getX, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, getY, VectorArg_getY, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, getZ, VectorArg_getZ, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, setX, VectorArg_setX, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, setY, VectorArg_setY, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, setZ, VectorArg_setZ, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, add, VectorArg_add, ZEND_ACC_PUBLIC) /**/
    PHP_ME(VectorObject, scale, VectorArg_scale, ZEND_ACC_PUBLIC) /**/
    PHP_FE_END};
```

4. Write C++ class like

```cpp
// Vector.hpp
#pragma once

class Vector
{
private:
    double x, y, z, w;
public:
    Vector() {}
    Vector(double x, double y, double z);

    double getX() const;
    double getY() const;
    double getZ() const;
    double getW() const;

    void setX(double value);
    void setY(double value);
    void setZ(double value);
    void setW(double value);

    double length() const;
    double distanceTo(const Vector &v);

    Vector substract(const Vector &v);
    Vector add(const Vector &v);
    Vector scale(double s);
    Vector transform(const Matrix4& mat);
};
```

```cpp
// Vector.cpp
#include <math.h>
#include "include\Vector.hpp"

Vector::Vector() {}
Vector::Vector(double x, double y, double z) {
    this->x = x;
    this->y = y;
    this->z = z;
    this->w = 0;
}

double Vector::getX() const { return x; }
double Vector::getY() const { return y; }
double Vector::getZ() const { return z; }
double Vector::getW() const { return w; }

void Vector::setX(double value) { x = value; }
void Vector::setY(double value) { y = value; }
void Vector::setZ(double value) { z = value; }
void Vector::setW(double value) { w = value; }

double Vector::length() const
{
    return sqrt(x * x + y * y + z * z);
}

double Vector::distanceTo(const Vector &v) const
{
    return this->substract(v).length();
}

Vector Vector::substract(const Vector &v) const
{
    return Vector(x - v.getX(), y - v.getY(), z - v.getZ());
}

Vector Vector::add(const Vector &v) const
{
    return Vector(x + v.getX(), y + v.getY(), z + v.getZ());
}

Vector Vector::scale(double s) const
{
    return Vector(x * s, y * s, z * s);
}
```
