<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Psr\Http\Message\ResponseInterface;
use Smithy\Core\Exception\ApiException;
use Smithy\Core\Shape\OperationInterface;
use Smithy\Http\Message\Response;
use Smithy\Protocol\HttpErrorDeserializer;

/**
 * Create unmodeled errors containing AWS response metadata.
 */
abstract class AbstractAwsErrorDeserializer extends HttpErrorDeserializer
{
    protected function createUnmodeledError(
        OperationInterface $operation,
        ResponseInterface $response,
        ?string $discriminator,
        string $message,
        ?string $requestId,
        ?\Throwable $previous = null
    ): \Throwable
    {
        $errorClass = $operation->getServiceExceptionClass();
        if ($errorClass === ApiException::class) {
            $errorClass = AwsProtocolException::class;
        }
        if (!\is_a($errorClass, AwsProtocolException::class, true)) {
            return parent::createUnmodeledError(
                $operation,
                $response,
                $discriminator,
                $message,
                $requestId,
                $previous
            );
        }

        return new $errorClass(
            message: self::defaultMessage($response, $discriminator, $message),
            fault: $this->resolveFault($response),
            previous: $previous,
            awsErrorCode: $discriminator,
            requestId: $requestId,
            httpStatusCode: $response->getStatusCode()
        );
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    final protected static function normalizeHeaders(
        ResponseInterface $response
    ): array
    {
        if ($response instanceof Response) {
            return $response->getRawHeaders();
        }

        $normalized = [];
        foreach ($response->getHeaders() as $name => $values) {
            $normalized[\strtolower($name)] = [
                $name,
                \array_values($values)
            ];
        }

        return $normalized;
    }
}
