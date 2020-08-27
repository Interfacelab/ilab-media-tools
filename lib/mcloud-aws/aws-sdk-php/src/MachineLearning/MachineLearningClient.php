<?php

namespace MediaCloud\Vendor\Aws\MachineLearning;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\CommandInterface;
use MediaCloud\Vendor\GuzzleHttp\Psr7\Uri;
use MediaCloud\Vendor\Psr\Http\Message\RequestInterface;

/**
 * Amazon Machine Learning client.
 *
 * @method \MediaCloud\Vendor\Aws\Result addTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createBatchPrediction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createBatchPredictionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataSourceFromRDS(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDataSourceFromRDSAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataSourceFromRedshift(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDataSourceFromRedshiftAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDataSourceFromS3(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDataSourceFromS3Async(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEvaluation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEvaluationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createMLModel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createMLModelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createRealtimeEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createRealtimeEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteBatchPrediction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteBatchPredictionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDataSource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDataSourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEvaluation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEvaluationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteMLModel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteMLModelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteRealtimeEndpoint(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteRealtimeEndpointAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeBatchPredictions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeBatchPredictionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDataSources(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDataSourcesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvaluations(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEvaluationsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeMLModels(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeMLModelsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeTags(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeTagsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getBatchPrediction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getBatchPredictionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getDataSource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getDataSourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getEvaluation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getEvaluationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result getMLModel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise getMLModelAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result predict(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise predictAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateBatchPrediction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateBatchPredictionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateDataSource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateDataSourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateEvaluation(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateEvaluationAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result updateMLModel(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise updateMLModelAsync(array $args = [])
 */
class MachineLearningClient extends AwsClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $list = $this->getHandlerList();
        $list->appendBuild($this->predictEndpoint(), 'ml.predict_endpoint');
    }

    /**
     * Changes the endpoint of the Predict operation to the provided endpoint.
     *
     * @return callable
     */
    private function predictEndpoint()
    {
        return static function (callable $handler) {
            return function (
                CommandInterface $command,
                RequestInterface $request = null
            ) use ($handler) {
                if ($command->getName() === 'Predict') {
                    $request = $request->withUri(new Uri($command['PredictEndpoint']));
                }
                return $handler($command, $request);
            };
        };
    }
}
