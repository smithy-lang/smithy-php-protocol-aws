<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Psr\Http\Message\ResponseInterface;

/**
 * Deserialize Rest JSON errors with HTTP-bound members.
 */
final class RestJsonErrorDeserializer extends AbstractAwsErrorDeserializer
{
    use HttpBoundErrorMembersTrait;

    /**
     * @return array{
     *     discriminator: ?string,
     *     message: string,
     *     body: string,
     *     requestId: ?string
     * }
     */
    protected function parseError(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $envelope = JsonErrorEnvelopeParser::parse($body);

        // Rest JSON prefers the header discriminator over body fields.
        $rawType = $response->getHeaderLine('x-amzn-errortype');
        if ($rawType === '') {
            $candidate = $envelope['type'] ?? $envelope['code'];
            $rawType = \is_string($candidate) ? $candidate : 'Unknown';
        }

        $requestId = $response->getHeaderLine('x-amzn-requestid');
        if ($requestId === '') {
            $requestId = $response->getHeaderLine('x-amz-request-id');
        }

        return [
            'discriminator' =>
                JsonErrorEnvelopeParser::normalizeErrorType($rawType),
            'message' => $envelope['message'],
            'body' => $body,
            'requestId' => $requestId !== '' ? $requestId : null
        ];
    }
}
