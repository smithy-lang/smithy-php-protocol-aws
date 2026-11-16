<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Psr\Http\Message\ResponseInterface;
use Smithy\Core\Serde\Schema;
use Smithy\Http\Message\StringRequestStream;
use Smithy\Protocol\HttpBindingDeserializer;
use Smithy\Protocol\HttpStructBindingExtension;
use Smithy\Protocol\HttpStructBindingExtensionProvider;

/**
 * Deserialize error members through their HTTP bindings.
 *
 * @internal Shared by the REST JSON and REST XML error deserializers.
 */
trait HttpBoundErrorMembersTrait
{
    /**
     * @param array<int, mixed> $members Positional member values.
     *
     * @return void
     */
    protected function deserializeMembers(
        Schema $schema,
        array &$members,
        ResponseInterface $response,
        string $body
    ): void
    {
        static $bindingKey = null;
        $bindingKey ??= HttpStructBindingExtensionProvider::$KEY
            ?? HttpStructBindingExtensionProvider::key();

        /** @var HttpStructBindingExtension|null $bindingInfo */
        $bindingInfo = $schema->getExtension($bindingKey);
        if ($bindingInfo === null) {
            parent::deserializeMembers(
                $schema,
                $members,
                $response,
                $body
            );

            return;
        }

        $deserializer = new HttpBindingDeserializer(
            $this->codec,
            $bindingInfo,
            self::normalizeHeaders($response),
            new StringRequestStream($body),
            $response->getStatusCode()
        );
        $deserializer->deserialize($schema, $members);
    }
}
