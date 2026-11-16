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
 */
class AwsProtocolException extends ApiException
{
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
}
