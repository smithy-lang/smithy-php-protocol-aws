# Smithy PHP AWS Protocols

AWS protocol implementations for Smithy PHP: AWS JSON 1.0 and 1.1, REST JSON,
REST XML, AWS Query, and EC2 Query. Requires PHP 8.2 or later.

This package owns the `Smithy\Protocol\Aws` namespace, a sub-namespace of
`smithy/protocol`. Classes keep their `Smithy\Protocol\Aws\*` names.

## Installation

```shell
composer require smithy/protocol-aws
```

## AWS errors

Unmodeled errors are built from the operation's service exception class when
it implements `AwsErrorInterface`, and from `AwsProtocolException` otherwise.
The deserializer calls `fromAwsError()` with the AWS error code, request ID and
HTTP status, and calls `setRetryAfter()` when the response has an
`x-amz-retry-after` hint. Modeled errors that implement `AwsErrorInterface` receive the same
metadata through `setAwsErrorMetadata()` after their members are
deserialized; the envelope message becomes the exception message when the
modeled message is empty. `AwsErrorRetryTrait` supplies the status-based
retry classification, so SDK base exceptions can implement the interface on
their own hierarchy.

Read the metadata with `getAwsErrorCode()`, `getAwsRequestId()` and
`getHttpStatusCode()`. Errors that implement `AwsExtendedRequestIdInterface`
(including `AwsProtocolException`) also carry an extended request ID, such as
the Amazon S3 `x-amz-id-2` value, through `getExtendedRequestId()`.

## AWS JSON

`AwsJson1_0Protocol` implements `aws.protocols#awsJson1_0` with AWS JSON RPC
framing, the `application/x-amz-json-1.0` content type, and AWS JSON error
envelopes.

```php
use Smithy\Protocol\Aws\AwsJson1_0Protocol;

$protocol = new AwsJson1_0Protocol();
```

`AwsJson1_1Protocol` implements `aws.protocols#awsJson1_1` with AWS JSON RPC
framing, the `application/x-amz-json-1.1` content type, and AWS JSON error
envelopes.

```php
use Smithy\Protocol\Aws\AwsJson1_1Protocol;

$protocol = new AwsJson1_1Protocol();
```

## AWS Rest JSON

`RestJsonProtocol` implements `aws.protocols#restJson1` with Smithy HTTP
bindings, the JSON codec, and AWS JSON error envelopes.

```php
use Smithy\Protocol\Aws\RestJsonProtocol;

$protocol = new RestJsonProtocol();
```

## AWS Rest XML

`RestXmlProtocol` implements `aws.protocols#restXml` with Smithy HTTP bindings,
the XML codec, and AWS XML error envelopes.

```php
use Smithy\Protocol\Aws\RestXmlProtocol;

$protocol = new RestXmlProtocol();
```

## AWS Query

`AwsQueryProtocol` implements `aws.protocols#awsQuery` with form URL-encoded
requests and XML responses.

```php
use Smithy\Protocol\Aws\AwsQueryProtocol;

$protocol = new AwsQueryProtocol('2020-01-08');
```

Pass the service version from the Smithy service shape. The protocol sends it
with the operation action in every request body.

## EC2 Query

`Ec2QueryProtocol` implements `aws.protocols#ec2Query` with EC2-specific form
member names, flattened lists, and XML response wrappers.

```php
use Smithy\Protocol\Aws\Ec2QueryProtocol;

$protocol = new Ec2QueryProtocol('2020-01-08');
```

Pass the service version from the Smithy service shape.

## Dependencies

This package depends on `smithy/codec`, `smithy/core`, `smithy/http`,
`smithy/protocol`, `psr/http-message`, `ext-libxml`, and `ext-simplexml`.
