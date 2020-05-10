<?php

namespace SampleExtension;

/** @functions */
interface Functions
{
    public const A = 100;
    public const B = 200;
    public const C = 300;
    public const D = 400;
    public const E = 500;

    public function function1(float $value): Color;
    public function function2(float $value): Color;
    public function function3(float $value): Color;
    public function function4(float $value): Color;
}

/** @final @class */
interface Color
{
    public function __construct(int $r, int $g, int $b, int $a);
    public function getR(): int;
    public function getG(): int;
    public function getB(): int;
    public function getA(): int;
    public function setR(int $value);
    public function setG(int $value);
    public function setB(int $value);
    public function setA(int $value);
    public function add(Color $color): Color;
    public function scale(float $value): Color;
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
