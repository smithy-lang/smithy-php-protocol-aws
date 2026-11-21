<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Exception\ApiException;
use Smithy\Core\Exception\ErrorFault;

/**
 * Base exception for errors returned by AWS protocols.
 *
 * Carries the AWS error code, request ID, extended request ID and HTTP
 * status, and takes its retry classification from
 * {@see AwsErrorRetryTrait}.
 *
 * @phpstan-consistent-constructor
 */
class AwsProtocolException extends ApiException implements
    AwsErrorInterface,
    AwsExtendedRequestIdInterface
{
    use AwsErrorRetryTrait;

    private ?string $extendedRequestId = null;

    /**
     * Create an AWS protocol error with response metadata.
     *
     * @return void
     */
    public function __construct(
        string $message = '',
        ErrorFault $fault = ErrorFault::Other,
        ?\Throwable $previous = null,
        private ?string $awsErrorCode = null,
        private ?string $requestId = null,
        private ?int $httpStatusCode = null
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

    /** @inheritDoc */
    public function getExtendedRequestId(): ?string
    {
        return $this->extendedRequestId;
    }

    /** @inheritDoc */
    public function setExtendedRequestId(?string $extendedRequestId): void
    {
        $this->extendedRequestId = $extendedRequestId;
    }

    /**
     * Create an AWS protocol error from response metadata.
     */
    public static function fromAwsError(
        string $message,
        ErrorFault $fault,
        ?\Throwable $previous,
        ?string $awsErrorCode,
        ?string $requestId,
        ?int $httpStatusCode
    ): static
    {
        return new static($message, $fault, $previous, $awsErrorCode, $requestId, $httpStatusCode);
    }

    /**
     * Set the AWS error metadata of a deserialized error.
     */
    public function setAwsErrorMetadata(
        string $message,
        ?string $awsErrorCode,
        ?string $requestId,
        ?int $httpStatusCode
    ): void
    {
        if ($this->message === '') {
            $this->message = $message;
        }
        $this->awsErrorCode = $awsErrorCode;
        $this->requestId = $requestId;
        $this->httpStatusCode = $httpStatusCode;
    }
}
