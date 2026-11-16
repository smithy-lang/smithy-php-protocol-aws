<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Implement the AWS JSON 1.0 protocol.
 */
final class AwsJson1_0Protocol extends AbstractAwsJsonProtocol
{
    public const ID = 'aws.protocols#awsJson1_0';

    protected static string $contentType = 'application/x-amz-json-1.0';

    public function getId(): string
    {
        return self::ID;
    }
}
