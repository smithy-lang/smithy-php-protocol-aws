<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Codec\Json\JsonCodec;
use Smithy\Codec\Json\JsonCodecSettings;
use Smithy\Core\Serde\TimestampFormat;
use Smithy\Protocol\AbstractHttpBindingProtocol;
use Smithy\Protocol\ProtocolSettings;

/**
 * Implement the AWS Rest JSON protocol.
 */
final class RestJsonProtocol extends AbstractHttpBindingProtocol
{
    public const ID = 'aws.protocols#restJson1';

    protected static string $defaultContentType = 'application/json';

    public function __construct(
        ProtocolSettings $settings = new ProtocolSettings()
    )
    {
        $codec = new JsonCodec(
            new JsonCodecSettings(
                defaultTimestampFormat: TimestampFormat::EpochSeconds
            )
        );

        parent::__construct(
            $codec,
            new RestJsonErrorDeserializer($codec),
            $settings
        );
    }

    public function getId(): string
    {
        return self::ID;
    }
}
