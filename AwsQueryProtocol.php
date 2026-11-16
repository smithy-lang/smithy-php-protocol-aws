<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Implement the AWS Query protocol.
 */
final class AwsQueryProtocol extends AbstractQueryProtocol
{
    public const ID = 'aws.protocols#awsQuery';

    public function getId(): string
    {
        return self::ID;
    }
}
