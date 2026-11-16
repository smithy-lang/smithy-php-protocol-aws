<?php

/*
 * Copyright Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Smithy\Protocol\Aws;

use Smithy\Core\Endpoint\Endpoint;
use Smithy\Core\Transport\{
    MessageExchangeInterface,
    TransportRequestInterface,
    TransportResponseInterface
};
use Smithy\Codec\FormUrlEncoded\{
    FormUrlEncodedCodec,
    FormUrlEncodedCodecSettings
};
use Smithy\Codec\Xml\{
    XmlCodec,
    XmlCodecSettings
};
use Smithy\Core\Serde\{
    Schema,
    SerializableStructInterface,
    TimestampFormat
};
use Smithy\Core\Shape\OperationInterface;
use Smithy\Http\Message\{
    HttpMessageExchange,
    HttpTransportRequest,
    HttpTransportResponse,
    Request
};
use Smithy\Protocol\ProtocolInterface;
use Smithy\Protocol\Utils\{
    ComponentUri,
    ResponseBody,
    ServiceEndpoint
};

/**
 * Share request and response behavior for the AWS query protocol family.
 */
abstract class AbstractQueryProtocol implements ProtocolInterface
{
    private const CONTENT_TYPE = 'application/x-www-form-urlencoded';

    private readonly FormUrlEncodedCodec $requestCodec;
    private readonly XmlCodec $responseCodec;
    private readonly AwsXmlErrorDeserializer $errorDeserializer;
    private readonly string $encodedVersion;
    /** @var array<string, string> `Action=...&Version=...` by runtime identity. */
    private array $actionBodyByOperation = [];
    /**
     * Response wrappers, output schema and members template by runtime
     * identity.
     *
     * @var array<string, array{0: list<string>, 1: Schema, 2: array<int, mixed>}>
     */
    private array $outputByOperation = [];

    /**
     * @param string $version Service version from the Smithy service shape.
     */
    public function __construct(string $version)
    {
        $this->requestCodec = new FormUrlEncodedCodec(
            $this->formUrlEncodedSettings()
        );
        $this->responseCodec = new XmlCodec(new XmlCodecSettings(
            defaultTimestampFormat: TimestampFormat::DateTime
        ));
        $this->errorDeserializer = new AwsXmlErrorDeserializer(
            $this->responseCodec
        );
        $this->encodedVersion = \rawurlencode($version);
    }

    /** @inheritDoc */
    public function getMessageExchange(): MessageExchangeInterface
    {
        return HttpMessageExchange::instance();
    }

    /**
     * Return the form URL-encoded behavior for this protocol.
     */
    protected function formUrlEncodedSettings(): FormUrlEncodedCodecSettings
    {
        return new FormUrlEncodedCodecSettings(
            defaultTimestampFormat: TimestampFormat::DateTime
        );
    }

    /**
     * Resolve the response wrapper path for an operation.
     *
     * @return list<string>
     */
    protected function responseWrappers(
        OperationInterface $operation
    ): array
    {
        $action = $this->responseWrapperBaseName($operation);

        return [$action . 'Response', $action . 'Result'];
    }

    /**
     * Resolve the operation name used in response wrappers.
     */
    protected function responseWrapperBaseName(
        OperationInterface $operation
    ): string
    {
        $action = $this->resolveAction($operation);
        $separator = \strrpos($action, '.');

        return $separator === false
            ? $action
            : \substr($action, $separator + 1);
    }

    public function serializeRequest(
        OperationInterface $operation,
        SerializableStructInterface $input
    ): TransportRequestInterface
    {
        $serializer = $this->requestCodec->acquireSerializer();
        try {
            $serializer->beginRootStruct($input->getSchema());
            $input->serializeMembers($serializer);
            $inputBody = $serializer->flush();
        } finally {
            $this->requestCodec->releaseSerializer($serializer);
        }

        $body = $this->actionBodyByOperation[$operation->getRuntimeIdentity()]
            ??= 'Action=' . \rawurlencode($this->resolveAction($operation))
                . '&Version=' . $this->encodedVersion;
        if ($inputBody !== '') {
            $body .= '&' . $inputBody;
        }

        $headers = [
            'content-type' => [
                'Content-Type',
                [self::CONTENT_TYPE]
            ],
            'content-length' => [
                'Content-Length',
                [(string) \strlen($body)]
            ]
        ];

        return new HttpTransportRequest(
            Request::fromNormalized(
                'POST',
                new ComponentUri(scheme: '', host: '', path: '/'),
                $headers,
                $body
            )
        );
    }

    public function setServiceEndpoint(
        TransportRequestInterface $request,
        Endpoint $endpoint
    ): TransportRequestInterface
    {
        return ServiceEndpoint::apply($request, $endpoint);
    }

    public function deserializeResponse(
        OperationInterface $operation,
        TransportResponseInterface $response
    ): SerializableStructInterface
    {
        $response = HttpTransportResponse::expect($response)->response;
        if ($response->getStatusCode() >= 300) {
            throw $this->errorDeserializer->deserialize(
                $operation,
                $response
            );
        }

        [$wrappers, $schema, $members] = $this->outputByOperation[
            $operation->getRuntimeIdentity()
        ] ??= $this->resolveOutput($operation);
        if ($members !== [] && ResponseBody::hasContent($response)) {
            $this->responseCodec
                ->createDeserializer($response->getBody(), $wrappers)
                ->deserialize($schema, $members);
        }

        return $operation->createOutput($members);
    }

    /**
     * @return array{0: list<string>, 1: Schema, 2: array<int, mixed>}
     */
    private function resolveOutput(OperationInterface $operation): array
    {
        $schema = $operation->getOutputSchema()->resolveTarget();

        return [
            $this->responseWrappers($operation),
            $schema,
            $this->responseCodec->createMembersBuffer($schema)
        ];
    }

    /**
     * Resolve the operation action sent in the request body.
     */
    private function resolveAction(OperationInterface $operation): string
    {
        $target = $operation->getRequestTarget();

        return $target !== null && $target !== ''
            ? $target
            : $operation->getName();
    }
}
