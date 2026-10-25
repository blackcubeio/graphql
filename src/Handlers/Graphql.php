<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Handlers;

use Blackcube\Dcore\Interfaces\JsonLdBuilderInterface;
use Blackcube\Graphql\Types\TypeFactory;
use GraphQL\Error\DebugFlag;
use GraphQL\Executor\Executor;
use GraphQL\GraphQL as GraphQLExecutor;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 handler for GraphQL endpoint.
 * Receives JSON body with query + variables, executes against schema, returns JSON.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Graphql implements RequestHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly JsonLdBuilderInterface $jsonLdBuilder,
        private readonly bool $debug = false,
        private readonly bool $fastSchema = true,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (is_array($body) === false) {
            $rawBody = (string) $request->getBody();
            $body = json_decode($rawBody, true) ?? [];
        }

        $query = $body['query'] ?? '';
        $variables = $body['variables'] ?? null;

        TypeFactory::setFastSchema($this->fastSchema);

        $schema = new Schema([
            'query' => TypeFactory::query(),
        ]);

        $context = [
            'request' => $request,
            'host' => $request->getUri()->getHost(),
            'jsonLdBuilder' => $this->jsonLdBuilder,
        ];

        $result = GraphQLExecutor::executeQuery(
            $schema,
            $query,
            null,
            $context,
            $variables,
            null,
            $this->readFieldValue(...)
        );

        $debugFlag = $this->debug === true
            ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
            : DebugFlag::NONE;
        $output = $result->toArray($debugFlag);

        $json = json_encode($output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $stream = $this->streamFactory->createStream($json);

        return $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }

    /**
     * Field value of a source object: its getXxx() or isXxx() when it has one, the default resolver otherwise.
     */
    private function readFieldValue(mixed $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        $value = null;
        $isRead = false;
        if (is_object($source) === true) {
            $getter = 'get' . ucfirst($info->fieldName);
            $isser = 'is' . ucfirst($info->fieldName);
            if (method_exists($source, $getter) === true) {
                $value = $source->$getter();
                $isRead = true;
            } elseif (method_exists($source, $isser) === true) {
                $value = $source->$isser();
                $isRead = true;
            }
        }

        if ($isRead === false) {
            $value = Executor::defaultFieldResolver($source, $args, $context, $info);
        }

        return $value;
    }
}
