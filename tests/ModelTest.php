<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JSONGraph\Model;

final class ModelTest extends TestCase
{
    public function testCanBeCreatedFromBlankArray(): void
    {
        $array = [];
        $this->assertInstanceOf(
            Model::class,
            (new Model($array))
        );
    }

    public function testCanBeCreatedFromBasicArray(): void
    {
        $array = [
            "a" => [
                "b",
                "c"
            ]
        ];
        $this->assertInstanceOf(
            Model::class,
            (new Model($array))
        );
    }

    public function testRefTypeReturnsValidReference(): void
    {
        $array = [
            "a" => [
                "b",
                "c"
            ],
            [
                '$type' => 'ref',
                'value' => [
                    "a"
                ]
            ]
        ];
        $this->assertSame($array["a"], (new Model($array))->get()[0]);
        $this->assertSame($array["a"][0], (new Model($array))->get()[0][0]);
    }
}
