<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Psr\Http\Message\ResponseInterface;

/**
 * Deserialize AWS XML error envelopes.
 */
class AwsXmlErrorDeserializer extends AbstractAwsErrorDeserializer
{
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
        $requestId = $response->getHeaderLine('x-amz-request-id');
        if ($requestId === '') {
            $requestId = $response->getHeaderLine('x-amzn-requestid');
        }

        $body = $response->getHeaderLine('Content-Length') === '0'
            ? ''
            : $response->getBody()->getContents();
        if ($body === '') {
            return [
                'discriminator' => 'Unknown',
                'message' => '',
                'body' => '',
                'requestId' => $requestId !== '' ? $requestId : null
            ];
        }

        $useInternalErrors = \libxml_use_internal_errors(true);
        try {
            $xml = new \SimpleXMLElement(
                $body,
                LIBXML_NOBLANKS | LIBXML_NONET | LIBXML_COMPACT
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Failed to parse XML error response: ' . $e->getMessage(),
                0,
                $e
            );
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($useInternalErrors);
        }

        $error = $xml;
        if (isset($xml->Error)) {
            $error = $xml->Error;
        } elseif (isset($xml->Errors->Error)) {
            $error = $xml->Errors->Error;
        }

        $errorBody = $error->asXML();
        if ($errorBody === false) {
            $errorBody = $body;
        }

        $bodyRequestId = (string) (
            $xml->RequestId
            ?? $xml->RequestID
            ?? $error->RequestId
            ?? $error->RequestID
            ?? ''
        );
        if ($bodyRequestId !== '') {
            $requestId = $bodyRequestId;
        }

        return [
            'discriminator' => (string) ($error->Code ?? 'Unknown'),
            'message' => (string) ($error->Message ?? ''),
            'body' => $errorBody,
            'requestId' => $requestId !== '' ? $requestId : null
        ];
    }
}
