<?php

namespace MediaCloud\Vendor\Aws\ConnectParticipant;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Connect Participant Service** service.
 * @method \MediaCloud\Vendor\Aws\Result completeAttachmentUpload(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise completeAttachmentUploadAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createParticipantConnection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createParticipantConnectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disconnectParticipant(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disconnectParticipantAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAttachment(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAttachmentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getTranscript(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getTranscriptAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendEvent(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendEventAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result sendMessage(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise sendMessageAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startAttachmentUpload(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startAttachmentUploadAsync(array $args = [])
 */
class ConnectParticipantClient extends AwsClient {}
