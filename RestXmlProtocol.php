<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Codec\Xml\XmlCodec;
use Smithy\Codec\Xml\XmlCodecSettings;
use Smithy\Core\Serde\TimestampFormat;
use Smithy\Protocol\AbstractHttpBindingProtocol;
use Smithy\Protocol\ProtocolSettings;

/**
 * Implement the AWS Rest XML protocol.
 */
final class RestXmlProtocol extends AbstractHttpBindingProtocol
{
    public const ID = 'aws.protocols#restXml';

    protected static string $defaultContentType = 'application/xml';

    public function __construct(
        ?string $defaultXmlNamespace = null,
        ProtocolSettings $settings = new ProtocolSettings()
    )
    {
        $codec = new XmlCodec(
            new XmlCodecSettings(
                defaultTimestampFormat: TimestampFormat::DateTime,
                defaultXmlNamespace: $defaultXmlNamespace
            )
        );

        parent::__construct(
            $codec,
            new RestXmlErrorDeserializer($codec),
            $settings
        );
    }

    public function getId(): string
    {
        return self::ID;
    }
}
