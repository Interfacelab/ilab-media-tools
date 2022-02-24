<?php

namespace MediaCloud\Vendor\Aws\CodeGuruReviewer;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodeGuru Reviewer** service.
 * @method \MediaCloud\Vendor\Aws\Result associateRepository(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise associateRepositoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createCodeReview(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCodeReviewAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCodeReview(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCodeReviewAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRecommendationFeedback(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRecommendationFeedbackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeRepositoryAssociation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeRepositoryAssociationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result disassociateRepository(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise disassociateRepositoryAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listCodeReviews(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listCodeReviewsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRecommendationFeedback(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRecommendationFeedbackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRecommendations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listRepositoryAssociations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listRepositoryAssociationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result putRecommendationFeedback(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise putRecommendationFeedbackAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result tagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result untagResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class CodeGuruReviewerClient extends AwsClient {}
