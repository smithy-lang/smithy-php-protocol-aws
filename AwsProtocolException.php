<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Exception\ApiException;
use Smithy\Core\Exception\ErrorFault;
use Smithy\Core\Retry\RetrySafety;

/**
 * Base exception for errors returned by AWS protocols.
 *
 * Refines the fault-based retry classification inherited from
 * {@see ApiException} with the HTTP status code: the transient server statuses
 * 500/502/503/504 are retryable, and 429 (Too Many Requests) is a retryable
 * throttle. An explicit classification set by codegen for a modeled
 * `retryable` error, or a `Retry-After` hint captured from the response, still
 * takes precedence.
 */
class AwsProtocolException extends ApiException
{
    /** HTTP statuses that are safe to retry regardless of fault. */
    private const RETRYABLE_STATUS = [500, 502, 503, 504];

    /** HTTP status signalling a throttle. */
    private const THROTTLE_STATUS = 429;

    /**
     * Create an AWS protocol error with response metadata.
     *
     * @return void
     */
    public function __construct(
        string $message = '',
        ErrorFault $fault = ErrorFault::Other,
        ?\Throwable $previous = null,
        private readonly ?string $awsErrorCode = null,
        private readonly ?string $requestId = null,
        private readonly ?int $httpStatusCode = null
    )
    {
        parent::__construct($message, $fault, $previous);
    }

    /**
     * Return the AWS error discriminator.
     */
    public function getAwsErrorCode(): ?string
    {
        return $this->awsErrorCode;
    }

    /**
     * Return the AWS response request ID.
     */
    public function getAwsRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Return the HTTP response status code.
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Set a server-suggested wait before retrying, in seconds.
     *
     * Called by error deserializers that read a `Retry-After` /
     * `x-amz-retry-after` header from the response.
     */
    public function setRetryAfter(?float $seconds): void
    {
        $this->retryAfter = $seconds;
    }

    /** @inheritDoc */
    public function getRetrySafety(): RetrySafety
    {
        if ($this->retrySafetyOverride !== null) {
            return $this->retrySafetyOverride;
        }

        if ($this->httpStatusCode === self::THROTTLE_STATUS
            || \in_array($this->httpStatusCode, self::RETRYABLE_STATUS, true)
        ) {
            return RetrySafety::Yes;
        }

        return parent::getRetrySafety();
    }

    /** @inheritDoc */
    public function isThrottle(): bool
    {
        if ($this->throttleOverride !== null) {
            return $this->throttleOverride;
        }

        return $this->httpStatusCode === self::THROTTLE_STATUS;
    }
}
