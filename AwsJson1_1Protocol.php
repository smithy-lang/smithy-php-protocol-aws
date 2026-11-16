<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Implement the AWS JSON 1.1 protocol.
 */
final class AwsJson1_1Protocol extends AbstractAwsJsonProtocol
{
    public const ID = 'aws.protocols#awsJson1_1';

    protected static string $contentType = 'application/x-amz-json-1.1';

    public function getId(): string
    {
        return self::ID;
    }
}
