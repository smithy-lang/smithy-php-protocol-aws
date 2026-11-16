<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

/**
 * Deserialize Rest XML errors with HTTP-bound members.
 */
final class RestXmlErrorDeserializer extends AwsXmlErrorDeserializer
{
    use HttpBoundErrorMembersTrait;
}
