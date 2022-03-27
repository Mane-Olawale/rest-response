<?php

namespace ManeOlawale\RestResponse\Tests;

use ManeOlawale\RestResponse\AbstractResponse;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function makeResponse(
        int $status = 200,
        array $headers = [],
        array $body = [],
        string $class = TestResponse::class
    ): AbstractResponse {
        return new $class($status, array_merge($headers, [
            'Content-Type' => 'application/json'
        ]), json_encode($body));
    }

    public function testStatusHelpers()
    {
        // Test ok method
        $ok = $this->makeResponse();
        $this->assertTrue($ok->ok());

        // Test unauthorized method
        $unauthorized = $this->makeResponse(401);
        $this->assertTrue($unauthorized->unauthorized());

        // Test forbidden method
        $forbidden = $this->makeResponse(403);
        $this->assertTrue($forbidden->forbidden());


        foreach (range(200, 299) as $value) {
            // Test success method
            $success = $this->makeResponse($value);
            $this->assertTrue($success->successful());
        }

        foreach (range(300, 399) as $value) {
            // Test success method
            $success = $this->makeResponse($value);
            $this->assertTrue($success->redirect());
        }

        foreach (range(400, 499) as $value) {
            // Test success method
            $success = $this->makeResponse($value);
            $this->assertTrue($success->clientError());
            $this->assertNotTrue($success->successful());
        }

        foreach (range(500, 599) as $value) {
            // Test success method
            $success = $this->makeResponse($value);
            $this->assertTrue($success->serverError());
            $this->assertNotTrue($success->successful());
        }
    }

    public function testArrayAccess()
    {
        $this->assertSame(
            $this->makeResponse(200, [], ['key' => 'value'])['key'],
            'value'
        );
    }

    public function testCallbacks()
    {
        $on_success = false;
        $on_error = false;
        $on_unauthorized = false;
        $on_forbidden = false;

        $this->makeResponse(200)->onSuccess(function ($response) use (&$on_success) {
            $on_success = true;
        });

        $this->makeResponse(400)->onError(function ($response) use (&$on_error) {
            $on_error = true;
        });

        $this->makeResponse(401)->onUnauthorized(function ($response) use (&$on_unauthorized) {
            $on_unauthorized = true;
        });

        $this->makeResponse(403)->onForbidden(function ($response) use (&$on_forbidden) {
            $on_forbidden = true;
        });

        $this->assertTrue($on_success);
        $this->assertTrue($on_error);
        $this->assertTrue($on_unauthorized);
        $this->assertTrue($on_forbidden);
    }

    public function testListResponse()
    {
        /**
         * @var TestListResponse
         */
        $list = $this->makeResponse(200, [], [
            'list' => [
                [
                    'name' => 'olawale',
                    'gender' => 'male'
                ],
                [
                    'name' => 'agnes',
                    'gender' => 'female'
                ],
            ]
        ], TestListResponse::class);

        $this->assertCount(2, $list);

        $each = [];
        $list->each(function ($item) use (&$each) {
            $each[] = $item['name'];
        });
        $this->assertSame([
            'olawale',
            'agnes'
        ], $each);

        $this->assertSame([
            'olawale',
            'agnes'
        ], $list->reduce(function ($result, $item) {
            $result[] = $item['name'];
            return $result;
        }, []));

        $this->assertSame([
            'olawalemale',
            'agnesfemale'
        ], $list->map(function ($item) {
            return $item['name'] . $item['gender'];
        }));
    }
}
