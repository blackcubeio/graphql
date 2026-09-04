<?php

declare(strict_types=1);

/**
 * PassthroughHandler.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Support\Stub;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Next handler in the middleware chain: answers a marker status code,
 * proving the middleware handed the request over.
 */
final class PassthroughHandler implements RequestHandlerInterface
{
    public const STATUS_CODE = 418;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(self::STATUS_CODE);
    }
}
