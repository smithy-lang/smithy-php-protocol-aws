<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Parse common fields from AWS JSON error envelopes.
 *
 * @internal Protocol deserializers choose error-discriminator precedence.
 */
final class JsonErrorEnvelopeParser
{
    /**
     * Decode the message and both error-code candidates.
     *
     * @return array{type: mixed, code: mixed, message: string}
     *
     * @throws \JsonException If a nonempty body contains malformed JSON.
     */
    public static function parse(string $body): array
    {
        $document = [];
        if ($body !== '') {
            $decoded = \json_decode(
                $body,
                true,
                512,
                \JSON_THROW_ON_ERROR
            );
            if (\is_array($decoded)) {
                $document = $decoded;
            }
        }

        return [
            'type' => $document['__type'] ?? null,
            'code' => $document['code'] ?? null,
            'message' => (string) (
                $document['message']
                ?? $document['Message']
                ?? ''
            )
        ];
    }

    /**
     * Remove legacy namespace prefixes and suffixes from an error type.
     */
    public static function normalizeErrorType(string $raw): string
    {
        $colonPos = \strpos($raw, ':');
        if ($colonPos !== false) {
            $raw = \substr($raw, 0, $colonPos);
        }

        $hashPos = \strrpos($raw, '#');
        if ($hashPos !== false) {
            $raw = \substr($raw, $hashPos + 1);
        }

        return $raw;
    }
}
