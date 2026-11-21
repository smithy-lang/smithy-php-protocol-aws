<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Contract for AWS errors that carry an extended request ID.
 *
 * Services such as Amazon S3 return a second request identifier in the
 * `x-amz-id-2` header or the `HostId` element of the error body. SDK
 * interceptors read it from the response and record it on the error.
 */
interface AwsExtendedRequestIdInterface
{
    /**
     * Return the extended request ID, or null when the response has none.
     */
    public function getExtendedRequestId(): ?string;

    /**
     * Set the extended request ID.
     */
    public function setExtendedRequestId(?string $extendedRequestId): void;
}
