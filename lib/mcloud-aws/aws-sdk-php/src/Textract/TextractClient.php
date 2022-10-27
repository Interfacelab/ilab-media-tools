<?php

namespace MediaCloud\Vendor\Aws\Textract;
use MediaCloud\Vendor\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Textract** service.
 * @method \MediaCloud\Vendor\Aws\Result analyzeDocument(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise analyzeDocumentAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result analyzeExpense(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise analyzeExpenseAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result analyzeID(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise analyzeIDAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result detectDocumentText(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise detectDocumentTextAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDocumentAnalysis(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDocumentAnalysisAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDocumentTextDetection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDocumentTextDetectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getExpenseAnalysis(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getExpenseAnalysisAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startDocumentAnalysis(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDocumentAnalysisAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startDocumentTextDetection(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDocumentTextDetectionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startExpenseAnalysis(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startExpenseAnalysisAsync(array $args = [])
 */
class TextractClient extends AwsClient {}
