<?php

namespace ManeOlawale\RestResponse;

use ArrayAccess;
use GuzzleHttp\Psr7\Response;

abstract class AbstractResponse extends Response implements ArrayAccess
{
    /**
     * Array of json response
     *
     * @var array
     */
    protected $responseArray;

    /**
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|resource|StreamInterface|null $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty,...
     *                                             a default will be used based on the status code)
     */
    public function __construct(
        $status = 200,
        array $headers = [],
        $body = null,
        $version = '1.1',
        $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);

        $body = $this->getBody()->__toString();
        $this->responseArray = [];

        if (strpos($this->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $array = @json_decode($body, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $this->responseArray = $array;
            }
        }

        $this->setUp($status, $headers, $body, $version, $reason);
    }

    /**
     * Set up the response
     *
     * @param string|int $status
     * @param array $headers
     * @param mixed $body
     * @param string $version
     * @param string $reason
     * @return void
     */
    protected function setUp(
        $status,
        array $headers,
        $body,
        $version,
        $reason
    ) {
        //
    }

    /**
     * Set value of an ofset
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        // Do nothing
    }

    /**
     * Check if offset exist
     *
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->responseArray[$offset]);
    }

    /**
     * Unset offset
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        // Do nothing
    }

    /**
     * Undocumented function
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->responseArray[$offset]) ? $this->responseArray[$offset] : null;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function body()
    {
        return (string) $this->getBody();
    }

    /**
     * Get the body of the response as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->responseArray;
    }


    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     */
    public function object()
    {
        return json_decode(json_encode($this->responseArray), false);
    }


    /**
     * Get the headers from the response.
     *
     * @return array
     */
    public function headers()
    {
        return $this->getHeaders();
    }


    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status()
    {
        return (int) $this->getStatusCode();
    }

    /**
     * Get the reason phrase of the response.
     *
     * @return string
     */
    public function reason()
    {
        return $this->getReasonPhrase();
    }


    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response was a 401 "Unauthorized" response.
     *
     * @return bool
     */
    public function unauthorized()
    {
        return $this->status() === 401;
    }

    /**
     * Determine if the response was a 403 "Forbidden" response.
     *
     * @return bool
     */
    public function forbidden()
    {
        return $this->status() === 403;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed()
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBody()->__toString();
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Execute the given callback if the request was successful.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onSuccess(callable $callback)
    {
        if ($this->successful()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Execute the given callback if forbidden.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onForbidden(callable $callback)
    {
        if ($this->forbidden()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Execute the given callback if unauthorized.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function onUnauthorized(callable $callback)
    {
        if ($this->unauthorized()) {
            $callback($this);
        }

        return $this;
    }
}
