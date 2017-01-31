<?php

namespace Test;

use Tymon\JWTAuth\JWTAuth;

/**
 * Class ApiTestCase
 */
abstract class ApiTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $token;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->token = null;
    }

    /**
     * Call GET API with authorization header
     *
     * @param string $uri
     * @param array  $headers
     *
     * @return $this
     */
    public function authGet(string $uri, array $headers = [])
    {
        return $this->get($uri, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Call POST API with authorization header
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return $this
     */
    public function authPost(string $uri, array $data = [], array $headers = [])
    {
        return $this->post($uri, $data, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Call PATCH API with authorization header
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return $this
     */
    public function authPatch(string $uri, array $data = [], array $headers = [])
    {
        return $this->post($uri, $data, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Call PUT API with authorization header
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return $this
     */
    public function authPut(string $uri, array $data = [], array $headers = [])
    {
        return $this->put($uri, $data, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * Call DELETE API with authorization header
     *
     * @param string $uri
     * @param array  $data
     * @param array  $headers
     *
     * @return $this
     */
    public function authDelete(string $uri, array $data = [], array $headers = [])
    {
        return $this->delete($uri, $data, array_merge($headers, $this->getAuthorizationHeader()));
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function login($username, $password)
    {
        $this->token = $this->generateToken($username, $password);
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return false|string
     */
    protected function generateToken($username, $password)
    {
        /** @var JWTAuth $jwtAuth */
        $jwtAuth = app(JWTAuth::class);

        return $jwtAuth->attempt(['username' => $username, 'password' => $password]);
    }

    /**
     * @return array
     */
    protected function getAuthorizationHeader()
    {
        return !empty($this->token) ? ['Authorization' => 'Bearer ' . $this->token] : [];
    }

    /**
     * @return array
     */
    protected function getResponseData()
    {
        return json_decode($this->response->getContent(), true);
    }

    /**
     * @param string $pattern
     */
    protected function seeJsonMatchingPattern($pattern)
    {
        $this->assertMatchesPattern(
            $pattern,
            $this->response->getContent(),
            'Response content does not match expected pattern'
        );
    }

    /**
     * @param array $data Expected data in headers
     */
    protected function seeInHeaders(array $data)
    {
        foreach ($data as $headerField => $expected) {
            static::assertEquals($expected, $this->response->headers->get($headerField));
        }
    }
}
