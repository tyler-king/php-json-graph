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

    public function testDocumentationExamples(): void
    {
        $array = [
            "environment" => [
                "production" => "https://production.example.com",
                "staging" => "https://staging.example.com",
                "development" => "http://localhost:8080"
            ],
            "url" => [
                '$type' => "ref",
                "value" => [
                    "environment",
                    [
                        '$type' => "env",
                        "value" => "ENVIRONMENT"
                    ]
                ]
            ]
        ];
        $this->assertSame($array["environment"]['production'], (new Model($array, [
            "ENVIRONMENT" => "production"
        ]))->get()['url']);
        putenv("ENVIRONMENT=production");
        $this->assertSame($array["environment"]['production'], (new Model($array))->get()['url']);
    }

    public function testDocumentationExamples2(): void
    {
        $array = [
            "contact" => "me@example.com",
            "route1" => [
                'contact' => [
                    '$type' => "ref",
                    "value" => [
                        "contact"
                    ]
                ]
            ]
        ];
        $this->assertSame($array["contact"], (new Model($array))->get()['route1']['contact']);
    }
}
