<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Retry\RetrySafety;

/**
 * Retry classification for AWS errors.
 *
 * Refines the fault-based retry classification inherited from
 * {@see \Smithy\Core\Exception\ApiException} with the HTTP status code: the
 * transient server statuses 500/502/503/504 are retryable, and 429 (Too Many
 * Requests) is a retryable throttle. An explicit classification set by codegen
 * for a modeled `retryable` error, or a `Retry-After` hint captured from the
 * response, still takes precedence.
 *
 * The using class extends `ApiException` and declares a `$httpStatusCode`
 * property (`?int`).
 */
trait AwsErrorRetryTrait
{
    /** HTTP statuses that are safe to retry regardless of fault. */
    private const RETRYABLE_STATUS = [500, 502, 503, 504];

    /** HTTP status signalling a throttle. */
    private const THROTTLE_STATUS = 429;

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
