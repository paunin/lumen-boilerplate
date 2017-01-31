<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * Logger to log all requests and responses
 */
class TrafficLogger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logger constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param mixed    $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle(Request $request, \Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        $statusTextMap = Response::$statusTexts;
        $statusCode    = $response->getStatusCode();
        $statusText    = array_key_exists($statusCode, $statusTextMap) ? $statusTextMap[$statusCode] : 'unknown status';

        $responseHeaders = $this->headersToArray($response->headers->all());
        $cookies         = $response->headers->getCookies();
        if (count($cookies) !== 0) {
            $responseHeaders['Set-Cookie'] = array_map(
                function ($cookie) {
                    return (string) $cookie;
                },
                $cookies
            );
        }

        $this->logger->info(
            'Incoming request and its response.',
            [
                'request'  => [
                    'method'         => $request->getMethod(),
                    'uri'            => $request->getUri(),
                    'serverProtocol' => $request->server->get('SERVER_PROTOCOL'),
                    'headers'        => $this->headersToArray($request->headers->all()),
                    'content'        => $request->getContent(),
                ],
                'response' => [
                    'protocolVersion' => 'HTTP/' . $response->getProtocolVersion(),
                    'statusCode'      => $statusCode,
                    'statusText'      => $statusText,
                    'headers'         => $responseHeaders,
                    'content'         => $response->getContent(),
                ],
            ]
        );

        return $response;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    protected function headersToArray(array $headers): array
    {
        $result = [];
        ksort($headers);
        foreach ($headers as $name => $values) {
            $result[implode('-', array_map('ucfirst', explode('-', $name)))] = $values;
        }

        return $result;
    }
}
