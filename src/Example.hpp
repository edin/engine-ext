#pragma once
#include <math.h>

class Vector
{
private:
    float x, y, z;

public:
    Vector() : x(0), y(0), z(0)
    {
    }

    Vector(float x, float y, float z) : x(x), y(y), z(z)
    {
    }

    float getX() const { return x; }
    float getY() const { return y; }
    float getZ() const { return z; }

    float setX(float value) { x = value; }
    float setY(float value) { y = value; }
    float setZ(float value) { z = value; }

    double distanceTo(const Vector &v) const
    {
        auto dx = v.getX() - x;
        auto dy = v.getY() - y;
        auto dz = v.getZ() - z;
        return sqrt(dx * dx + dy * dy + dz * dz);
    }

    Vector substract(const Vector &v) const
    {
        return Vector(x - v.getX(), y - v.getY(), z - v.getZ());
    }

    Vector add(const Vector &v) const
    {
        return Vector(x + v.getX(), y + v.getY(), z + v.getZ());
    }

    Vector scale(float s) const
    {
        return Vector(x * s, y * s, z * s);
    }
};

class Matrix3
{
private:
    float m[3][3];

public:
    Matrix3()
    {
        for (int j = 0; j < 3; j++)
        {
            for (int i = 0; i < 3; i++)
            {
                m[j][i] = 0;
            }
        }
    }

    void setAt(int row, int column, float value)
    {
        m[row][column] = value;
    }

    float getAt(int row, int column) const
    {
        return m[row][column];
    }

    int getRows() const { return 3; }
    int getColumns() const { return 3; }

    void assign(const Matrix3 &matrix)
    {
        for (int j = 0; j < 3; j++)
        {
            for (int i = 0; i < 3; i++)
            {
                m[j][i] = matrix.getAt(j, i);
            }
        }
    }

    Matrix3 scale(float scale) const
    {
        Matrix3 result;
        for (int j = 0; j < 3; j++)
        {
            for (int i = 0; i < 3; i++)
            {
                result.setAt(j, i, this->getAt(j, i) * scale);
            }
        }
        return result;
    }
};