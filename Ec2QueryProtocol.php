<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Codec\FormUrlEncoded\FormUrlEncodedCodecSettings;
use Smithy\Core\Serde\TimestampFormat;
use Smithy\Core\Shape\OperationInterface;

/**
 * Implement the EC2 Query protocol.
 */
final class Ec2QueryProtocol extends AbstractQueryProtocol
{
    public const ID = 'aws.protocols#ec2Query';

    public function getId(): string
    {
        return self::ID;
    }

    protected function formUrlEncodedSettings(): FormUrlEncodedCodecSettings
    {
        return new FormUrlEncodedCodecSettings(
            defaultTimestampFormat: TimestampFormat::DateTime,
            capitalizeResolvedWireNames: true,
            alwaysFlattenLists: true,
            supportsMaps: false
        );
    }

    protected function responseWrappers(
        OperationInterface $operation
    ): array
    {
        return [$this->responseWrapperBaseName($operation) . 'Response'];
    }
}
