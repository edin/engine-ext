#include "Example.hpp"
#include "Extension.hpp"

Extension getExtension()
{
    Extension extension("Extension", "Math");

    //TODO: Describe signature arguments
    //TODO: Describe union types, parameter names, passing by reference or passing by value,
    //      default values, nullable parameters, return types, built in types

    MethodSignature getFloat;
    MethodSignature setFloat;
    MethodSignature getInt;

    MethodSignature Vector_distanceTo;
    MethodSignature Vector_scale;
    MethodSignature Vector_substract;
    MethodSignature Vector_add;

    MethodSignature Matrix_scale;
    MethodSignature Matrix_setAt;
    MethodSignature Matrix_getAt;

    extension.addType<Vector>("Vector", [=](TypeInfo<Vector> &type) {
        type.addMethod(&Vector::getX, "getX", getFloat);
        type.addMethod(&Vector::getY, "getY", getFloat);
        type.addMethod(&Vector::getZ, "getZ", getFloat);

        type.addMethod(&Vector::setX, "setX", setFloat);
        type.addMethod(&Vector::setY, "setY", setFloat);
        type.addMethod(&Vector::setZ, "setZ", setFloat);

        type.addMethod(&Vector::distanceTo, "distanceTo", Vector_distanceTo);
        type.addMethod(&Vector::scale, "scale", Vector_scale);
        type.addMethod(&Vector::substract, "substract", Vector_substract);
        type.addMethod(&Vector::add, "add", Vector_add);
    });

    extension.addType<Matrix3>("Matrix3", [=](TypeInfo<Matrix3> &type) {
        type.setClassModifier(ClassModifier::Final);

        type.addMethod(&Matrix3::getRows, "getRows", getInt);
        type.addMethod(&Matrix3::getColumns, "getColumns", getInt);

        type.addMethod(&Matrix3::getAt, "getAt", Matrix_getAt);
        type.addMethod(&Matrix3::setAt, "setAt", Matrix_setAt);

        type.addMethod(&Matrix3::scale, "scale", Matrix_scale);
    });

    return extension;
};

int main()
{
    auto extension = getExtension();
};
