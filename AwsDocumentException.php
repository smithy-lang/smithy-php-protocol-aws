<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Exception\ApiException;
use Smithy\Core\Exception\ErrorFault;
use Smithy\Core\Serde\Schema;
use Smithy\Core\Serde\SerializableStructInterface;
use Smithy\Core\Shape\Document\StructDocument;
use Smithy\Core\Shape\Exception\DocumentExceptionTrait;
use Smithy\Core\Shape\ShapeType;

/**
 * Schema-backed modeled exception for errors returned by AWS protocols.
 *
 * Behaves like {@see \Smithy\Core\Shape\Exception\DocumentException} and also
 * carries the AWS error code, request ID, extended request ID and HTTP
 * status, with the retry classification of {@see AwsErrorRetryTrait}.
 */
final class AwsDocumentException extends ApiException implements
    SerializableStructInterface,
    AwsErrorInterface,
    AwsExtendedRequestIdInterface
{
    use AwsErrorRetryTrait;
    use DocumentExceptionTrait;

    private StructDocument $document;

    private ?string $extendedRequestId = null;

    /**
     * Create a schema-backed modeled AWS exception.
     *
     * @param array<int, mixed> $members Positional modeled error members.
     */
    public function __construct(
        Schema $schema,
        array $members,
        string $message = '',
        ErrorFault $fault = ErrorFault::Other,
        ?\Throwable $previous = null,
        private ?string $awsErrorCode = null,
        private ?string $requestId = null,
        private ?int $httpStatusCode = null
    )
    {
        parent::__construct($message, $fault, $previous);
        $this->document = StructDocument::fromMembers($schema, $members);
    }

    /**
     * Create an error without modeled members from response metadata.
     */
    public static function fromAwsError(
        string $message,
        ErrorFault $fault,
        ?\Throwable $previous,
        ?string $awsErrorCode,
        ?string $requestId,
        ?int $httpStatusCode
    ): self
    {
        return new self(
            new Schema(id: 'smithy.api#Unit', type: ShapeType::Structure),
            [],
            $message,
            $fault,
            $previous,
            $awsErrorCode,
            $requestId,
            $httpStatusCode
        );
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

    /**
     * Return a copy with one modeled member replaced.
     */
    public function withMemberValue(Schema $member, mixed $value): static
    {
        $copy = new self(
            $this->getSchema(),
            [],
            $this->getMessage(),
            $this->fault,
            $this->getPrevious(),
            $this->awsErrorCode,
            $this->requestId,
            $this->httpStatusCode
        );
        $copy->document = $this->document->withMemberValue($member, $value);
        $copy->extendedRequestId = $this->extendedRequestId;
        $copy->retryAfter = $this->retryAfter;
        $copy->retrySafetyOverride = $this->retrySafetyOverride;
        $copy->throttleOverride = $this->throttleOverride;

        return $copy;
    }
}
