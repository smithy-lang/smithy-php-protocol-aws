<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Exception\ErrorFault;

/**
 * Contract for exceptions that carry AWS error metadata.
 *
 * Error deserializers build any exception class implementing this interface
 * with the AWS error code and message. Errors read from an HTTP response
 * also carry its request ID and HTTP status, and a `Retry-After` hint when
 * the response has one. Errors read from an event stream carry the error
 * code and message, with a null request ID and HTTP status. Modeled errors
 * receive the same metadata through {@see setAwsErrorMetadata()} after
 * their members are deserialized.
 */
interface AwsErrorInterface
{
    public static function fromAwsError(
        string $message,
        ErrorFault $fault,
        ?\Throwable $previous,
        ?string $awsErrorCode,
        ?string $requestId,
        ?int $httpStatusCode
    ): static;

    /**
     * Return the AWS error discriminator.
     */
    public function getAwsErrorCode(): ?string;

    /**
     * Return the AWS response request ID.
     */
    public function getAwsRequestId(): ?string;

    /**
     * Return the HTTP response status code.
     */
    public function getHttpStatusCode(): ?int;

    /**
     * Set the AWS error metadata of a deserialized error.
     *
     * Replaces the error code, request ID and HTTP status. `$message`
     * becomes the exception message when the current message is empty.
     */
    public function setAwsErrorMetadata(
        string $message,
        ?string $awsErrorCode,
        ?string $requestId,
        ?int $httpStatusCode
    ): void;

    public function setRetryAfter(?float $seconds): void;
}
