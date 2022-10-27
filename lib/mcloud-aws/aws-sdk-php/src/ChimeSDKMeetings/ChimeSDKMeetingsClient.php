<?php

namespace MediaCloud\Vendor\Aws\ChimeSDKMeetings;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Chime SDK Meetings** service.
 * @method \MediaCloud\Vendor\Aws\Result batchCreateAttendee(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise batchCreateAttendeeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createAttendee(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createAttendeeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMeeting(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMeetingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMeetingWithAttendees(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMeetingWithAttendeesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteAttendee(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteAttendeeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteMeeting(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteMeetingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getAttendee(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getAttendeeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMeeting(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMeetingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listAttendees(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listAttendeesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startMeetingTranscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startMeetingTranscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopMeetingTranscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopMeetingTranscriptionAsync(array $args = [])
 */
class ChimeSDKMeetingsClient extends AwsClient {}
