<?php

namespace MediaCloud\Vendor\Aws;

/**
 * Builds AWS clients based on configuration settings.
 *
 * @method \MediaCloud\Vendor\Aws\ACMPCA\ACMPCAClient createACMPCA(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionACMPCA(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AccessAnalyzer\AccessAnalyzerClient createAccessAnalyzer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAccessAnalyzer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Acm\AcmClient createAcm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAcm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AlexaForBusiness\AlexaForBusinessClient createAlexaForBusiness(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAlexaForBusiness(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Amplify\AmplifyClient createAmplify(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAmplify(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApiGateway\ApiGatewayClient createApiGateway(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApiGateway(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApiGatewayManagementApi\ApiGatewayManagementApiClient createApiGatewayManagementApi(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApiGatewayManagementApi(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApiGatewayV2\ApiGatewayV2Client createApiGatewayV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApiGatewayV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AppConfig\AppConfigClient createAppConfig(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAppConfig(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AppMesh\AppMeshClient createAppMesh(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAppMesh(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AppSync\AppSyncClient createAppSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAppSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApplicationAutoScaling\ApplicationAutoScalingClient createApplicationAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApplicationAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApplicationDiscoveryService\ApplicationDiscoveryServiceClient createApplicationDiscoveryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApplicationDiscoveryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ApplicationInsights\ApplicationInsightsClient createApplicationInsights(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionApplicationInsights(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Appstream\AppstreamClient createAppstream(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAppstream(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Athena\AthenaClient createAthena(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAthena(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AugmentedAIRuntime\AugmentedAIRuntimeClient createAugmentedAIRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAugmentedAIRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AutoScaling\AutoScalingClient createAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAutoScaling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\AutoScalingPlans\AutoScalingPlansClient createAutoScalingPlans(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionAutoScalingPlans(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Backup\BackupClient createBackup(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionBackup(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Batch\BatchClient createBatch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionBatch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Braket\BraketClient createBraket(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionBraket(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Budgets\BudgetsClient createBudgets(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionBudgets(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Chime\ChimeClient createChime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionChime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Cloud9\Cloud9Client createCloud9(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloud9(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudDirectory\CloudDirectoryClient createCloudDirectory(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudDirectory(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudFormation\CloudFormationClient createCloudFormation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudFormation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudFront\CloudFrontClient createCloudFront(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudFront(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudHSMV2\CloudHSMV2Client createCloudHSMV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudHSMV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudHsm\CloudHsmClient createCloudHsm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudHsm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudSearch\CloudSearchClient createCloudSearch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudSearch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudSearchDomain\CloudSearchDomainClient createCloudSearchDomain(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudSearchDomain(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudTrail\CloudTrailClient createCloudTrail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudTrail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudWatch\CloudWatchClient createCloudWatch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudWatch(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudWatchEvents\CloudWatchEventsClient createCloudWatchEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudWatchEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CloudWatchLogs\CloudWatchLogsClient createCloudWatchLogs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCloudWatchLogs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeArtifact\CodeArtifactClient createCodeArtifact(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeArtifact(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeBuild\CodeBuildClient createCodeBuild(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeBuild(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeCommit\CodeCommitClient createCodeCommit(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeCommit(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeDeploy\CodeDeployClient createCodeDeploy(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeDeploy(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeGuruProfiler\CodeGuruProfilerClient createCodeGuruProfiler(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeGuruProfiler(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeGuruReviewer\CodeGuruReviewerClient createCodeGuruReviewer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeGuruReviewer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodePipeline\CodePipelineClient createCodePipeline(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodePipeline(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeStar\CodeStarClient createCodeStar(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeStar(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeStarNotifications\CodeStarNotificationsClient createCodeStarNotifications(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeStarNotifications(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CodeStarconnections\CodeStarconnectionsClient createCodeStarconnections(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCodeStarconnections(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CognitoIdentity\CognitoIdentityClient createCognitoIdentity(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCognitoIdentity(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CognitoIdentityProvider\CognitoIdentityProviderClient createCognitoIdentityProvider(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCognitoIdentityProvider(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CognitoSync\CognitoSyncClient createCognitoSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCognitoSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Comprehend\ComprehendClient createComprehend(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionComprehend(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ComprehendMedical\ComprehendMedicalClient createComprehendMedical(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionComprehendMedical(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ComputeOptimizer\ComputeOptimizerClient createComputeOptimizer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionComputeOptimizer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ConfigService\ConfigServiceClient createConfigService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionConfigService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Connect\ConnectClient createConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ConnectParticipant\ConnectParticipantClient createConnectParticipant(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionConnectParticipant(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CostExplorer\CostExplorerClient createCostExplorer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCostExplorer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\CostandUsageReportService\CostandUsageReportServiceClient createCostandUsageReportService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionCostandUsageReportService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DAX\DAXClient createDAX(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDAX(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DLM\DLMClient createDLM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDLM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DataExchange\DataExchangeClient createDataExchange(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDataExchange(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DataPipeline\DataPipelineClient createDataPipeline(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDataPipeline(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DataSync\DataSyncClient createDataSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDataSync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DatabaseMigrationService\DatabaseMigrationServiceClient createDatabaseMigrationService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDatabaseMigrationService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Detective\DetectiveClient createDetective(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDetective(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DeviceFarm\DeviceFarmClient createDeviceFarm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDeviceFarm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DirectConnect\DirectConnectClient createDirectConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDirectConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DirectoryService\DirectoryServiceClient createDirectoryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDirectoryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DocDB\DocDBClient createDocDB(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDocDB(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DynamoDb\DynamoDbClient createDynamoDb(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDynamoDb(array $args = [])
 * @method \MediaCloud\Vendor\Aws\DynamoDbStreams\DynamoDbStreamsClient createDynamoDbStreams(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionDynamoDbStreams(array $args = [])
 * @method \MediaCloud\Vendor\Aws\EBS\EBSClient createEBS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEBS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\EC2InstanceConnect\EC2InstanceConnectClient createEC2InstanceConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEC2InstanceConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\EKS\EKSClient createEKS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEKS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Ec2\Ec2Client createEc2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEc2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Ecr\EcrClient createEcr(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEcr(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Ecs\EcsClient createEcs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEcs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Efs\EfsClient createEfs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEfs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElastiCache\ElastiCacheClient createElastiCache(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElastiCache(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticBeanstalk\ElasticBeanstalkClient createElasticBeanstalk(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticBeanstalk(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticInference\ElasticInferenceClient createElasticInference(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticInference(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticLoadBalancing\ElasticLoadBalancingClient createElasticLoadBalancing(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticLoadBalancing(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client createElasticLoadBalancingV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticLoadBalancingV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticTranscoder\ElasticTranscoderClient createElasticTranscoder(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticTranscoder(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ElasticsearchService\ElasticsearchServiceClient createElasticsearchService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionElasticsearchService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Emr\EmrClient createEmr(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEmr(array $args = [])
 * @method \MediaCloud\Vendor\Aws\EventBridge\EventBridgeClient createEventBridge(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionEventBridge(array $args = [])
 * @method \MediaCloud\Vendor\Aws\FMS\FMSClient createFMS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionFMS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\FSx\FSxClient createFSx(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionFSx(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Firehose\FirehoseClient createFirehose(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionFirehose(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ForecastQueryService\ForecastQueryServiceClient createForecastQueryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionForecastQueryService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ForecastService\ForecastServiceClient createForecastService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionForecastService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\FraudDetector\FraudDetectorClient createFraudDetector(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionFraudDetector(array $args = [])
 * @method \MediaCloud\Vendor\Aws\GameLift\GameLiftClient createGameLift(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGameLift(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Glacier\GlacierClient createGlacier(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGlacier(array $args = [])
 * @method \MediaCloud\Vendor\Aws\GlobalAccelerator\GlobalAcceleratorClient createGlobalAccelerator(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGlobalAccelerator(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Glue\GlueClient createGlue(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGlue(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Greengrass\GreengrassClient createGreengrass(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGreengrass(array $args = [])
 * @method \MediaCloud\Vendor\Aws\GroundStation\GroundStationClient createGroundStation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGroundStation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\GuardDuty\GuardDutyClient createGuardDuty(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionGuardDuty(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Health\HealthClient createHealth(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionHealth(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Honeycode\HoneycodeClient createHoneycode(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionHoneycode(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IVS\IVSClient createIVS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIVS(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Iam\IamClient createIam(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIam(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IdentityStore\IdentityStoreClient createIdentityStore(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIdentityStore(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ImportExport\ImportExportClient createImportExport(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionImportExport(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Inspector\InspectorClient createInspector(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionInspector(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoT1ClickDevicesService\IoT1ClickDevicesServiceClient createIoT1ClickDevicesService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoT1ClickDevicesService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoT1ClickProjects\IoT1ClickProjectsClient createIoT1ClickProjects(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoT1ClickProjects(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTAnalytics\IoTAnalyticsClient createIoTAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTEvents\IoTEventsClient createIoTEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTEventsData\IoTEventsDataClient createIoTEventsData(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTEventsData(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTJobsDataPlane\IoTJobsDataPlaneClient createIoTJobsDataPlane(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTJobsDataPlane(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTSecureTunneling\IoTSecureTunnelingClient createIoTSecureTunneling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTSecureTunneling(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTSiteWise\IoTSiteWiseClient createIoTSiteWise(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTSiteWise(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IoTThingsGraph\IoTThingsGraphClient createIoTThingsGraph(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIoTThingsGraph(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Iot\IotClient createIot(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIot(array $args = [])
 * @method \MediaCloud\Vendor\Aws\IotDataPlane\IotDataPlaneClient createIotDataPlane(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionIotDataPlane(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Kafka\KafkaClient createKafka(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKafka(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Kinesis\KinesisClient createKinesis(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesis(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisAnalytics\KinesisAnalyticsClient createKinesisAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisAnalyticsV2\KinesisAnalyticsV2Client createKinesisAnalyticsV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisAnalyticsV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisVideo\KinesisVideoClient createKinesisVideo(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisVideo(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisVideoArchivedMedia\KinesisVideoArchivedMediaClient createKinesisVideoArchivedMedia(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisVideoArchivedMedia(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisVideoMedia\KinesisVideoMediaClient createKinesisVideoMedia(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisVideoMedia(array $args = [])
 * @method \MediaCloud\Vendor\Aws\KinesisVideoSignalingChannels\KinesisVideoSignalingChannelsClient createKinesisVideoSignalingChannels(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKinesisVideoSignalingChannels(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Kms\KmsClient createKms(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionKms(array $args = [])
 * @method \MediaCloud\Vendor\Aws\LakeFormation\LakeFormationClient createLakeFormation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLakeFormation(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Lambda\LambdaClient createLambda(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLambda(array $args = [])
 * @method \MediaCloud\Vendor\Aws\LexModelBuildingService\LexModelBuildingServiceClient createLexModelBuildingService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLexModelBuildingService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\LexRuntimeService\LexRuntimeServiceClient createLexRuntimeService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLexRuntimeService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\LicenseManager\LicenseManagerClient createLicenseManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLicenseManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Lightsail\LightsailClient createLightsail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionLightsail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MQ\MQClient createMQ(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMQ(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MTurk\MTurkClient createMTurk(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMTurk(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MachineLearning\MachineLearningClient createMachineLearning(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMachineLearning(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Macie\MacieClient createMacie(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMacie(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Macie2\Macie2Client createMacie2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMacie2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ManagedBlockchain\ManagedBlockchainClient createManagedBlockchain(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionManagedBlockchain(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MarketplaceCatalog\MarketplaceCatalogClient createMarketplaceCatalog(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMarketplaceCatalog(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MarketplaceCommerceAnalytics\MarketplaceCommerceAnalyticsClient createMarketplaceCommerceAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMarketplaceCommerceAnalytics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MarketplaceEntitlementService\MarketplaceEntitlementServiceClient createMarketplaceEntitlementService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMarketplaceEntitlementService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MarketplaceMetering\MarketplaceMeteringClient createMarketplaceMetering(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMarketplaceMetering(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaConnect\MediaConnectClient createMediaConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaConnect(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaConvert\MediaConvertClient createMediaConvert(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaConvert(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaLive\MediaLiveClient createMediaLive(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaLive(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaPackage\MediaPackageClient createMediaPackage(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaPackage(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaPackageVod\MediaPackageVodClient createMediaPackageVod(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaPackageVod(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaStore\MediaStoreClient createMediaStore(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaStore(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaStoreData\MediaStoreDataClient createMediaStoreData(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaStoreData(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MediaTailor\MediaTailorClient createMediaTailor(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMediaTailor(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MigrationHub\MigrationHubClient createMigrationHub(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMigrationHub(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MigrationHubConfig\MigrationHubConfigClient createMigrationHubConfig(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMigrationHubConfig(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Mobile\MobileClient createMobile(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionMobile(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Neptune\NeptuneClient createNeptune(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionNeptune(array $args = [])
 * @method \MediaCloud\Vendor\Aws\NetworkManager\NetworkManagerClient createNetworkManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionNetworkManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\OpsWorks\OpsWorksClient createOpsWorks(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionOpsWorks(array $args = [])
 * @method \MediaCloud\Vendor\Aws\OpsWorksCM\OpsWorksCMClient createOpsWorksCM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionOpsWorksCM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Organizations\OrganizationsClient createOrganizations(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionOrganizations(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Outposts\OutpostsClient createOutposts(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionOutposts(array $args = [])
 * @method \MediaCloud\Vendor\Aws\PI\PIClient createPI(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPI(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Personalize\PersonalizeClient createPersonalize(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPersonalize(array $args = [])
 * @method \MediaCloud\Vendor\Aws\PersonalizeEvents\PersonalizeEventsClient createPersonalizeEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPersonalizeEvents(array $args = [])
 * @method \MediaCloud\Vendor\Aws\PersonalizeRuntime\PersonalizeRuntimeClient createPersonalizeRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPersonalizeRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Pinpoint\PinpointClient createPinpoint(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPinpoint(array $args = [])
 * @method \MediaCloud\Vendor\Aws\PinpointEmail\PinpointEmailClient createPinpointEmail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPinpointEmail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\PinpointSMSVoice\PinpointSMSVoiceClient createPinpointSMSVoice(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPinpointSMSVoice(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Polly\PollyClient createPolly(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPolly(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Pricing\PricingClient createPricing(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionPricing(array $args = [])
 * @method \MediaCloud\Vendor\Aws\QLDB\QLDBClient createQLDB(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionQLDB(array $args = [])
 * @method \MediaCloud\Vendor\Aws\QLDBSession\QLDBSessionClient createQLDBSession(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionQLDBSession(array $args = [])
 * @method \MediaCloud\Vendor\Aws\QuickSight\QuickSightClient createQuickSight(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionQuickSight(array $args = [])
 * @method \MediaCloud\Vendor\Aws\RAM\RAMClient createRAM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRAM(array $args = [])
 * @method \MediaCloud\Vendor\Aws\RDSDataService\RDSDataServiceClient createRDSDataService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRDSDataService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Rds\RdsClient createRds(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRds(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Redshift\RedshiftClient createRedshift(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRedshift(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Rekognition\RekognitionClient createRekognition(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRekognition(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ResourceGroups\ResourceGroupsClient createResourceGroups(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionResourceGroups(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ResourceGroupsTaggingAPI\ResourceGroupsTaggingAPIClient createResourceGroupsTaggingAPI(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionResourceGroupsTaggingAPI(array $args = [])
 * @method \MediaCloud\Vendor\Aws\RoboMaker\RoboMakerClient createRoboMaker(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRoboMaker(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Route53\Route53Client createRoute53(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRoute53(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Route53Domains\Route53DomainsClient createRoute53Domains(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRoute53Domains(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Route53Resolver\Route53ResolverClient createRoute53Resolver(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionRoute53Resolver(array $args = [])
 * @method \MediaCloud\Vendor\Aws\S3\S3Client createS3(array $args = [])
 * @method \MediaCloud\Vendor\Aws\S3\S3MultiRegionClient createMultiRegionS3(array $args = [])
 * @method \MediaCloud\Vendor\Aws\S3Control\S3ControlClient createS3Control(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionS3Control(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SSO\SSOClient createSSO(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSSO(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SSOOIDC\SSOOIDCClient createSSOOIDC(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSSOOIDC(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SageMaker\SageMakerClient createSageMaker(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSageMaker(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SageMakerRuntime\SageMakerRuntimeClient createSageMakerRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSageMakerRuntime(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SavingsPlans\SavingsPlansClient createSavingsPlans(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSavingsPlans(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Schemas\SchemasClient createSchemas(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSchemas(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SecretsManager\SecretsManagerClient createSecretsManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSecretsManager(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SecurityHub\SecurityHubClient createSecurityHub(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSecurityHub(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ServerlessApplicationRepository\ServerlessApplicationRepositoryClient createServerlessApplicationRepository(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionServerlessApplicationRepository(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ServiceCatalog\ServiceCatalogClient createServiceCatalog(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionServiceCatalog(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ServiceDiscovery\ServiceDiscoveryClient createServiceDiscovery(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionServiceDiscovery(array $args = [])
 * @method \MediaCloud\Vendor\Aws\ServiceQuotas\ServiceQuotasClient createServiceQuotas(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionServiceQuotas(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Ses\SesClient createSes(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSes(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SesV2\SesV2Client createSesV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSesV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Sfn\SfnClient createSfn(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSfn(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Shield\ShieldClient createShield(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionShield(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Sms\SmsClient createSms(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSms(array $args = [])
 * @method \MediaCloud\Vendor\Aws\SnowBall\SnowBallClient createSnowBall(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSnowBall(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Sns\SnsClient createSns(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSns(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Sqs\SqsClient createSqs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSqs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Ssm\SsmClient createSsm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSsm(array $args = [])
 * @method \MediaCloud\Vendor\Aws\StorageGateway\StorageGatewayClient createStorageGateway(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionStorageGateway(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Sts\StsClient createSts(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSts(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Support\SupportClient createSupport(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSupport(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Swf\SwfClient createSwf(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSwf(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Synthetics\SyntheticsClient createSynthetics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionSynthetics(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Textract\TextractClient createTextract(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionTextract(array $args = [])
 * @method \MediaCloud\Vendor\Aws\TranscribeService\TranscribeServiceClient createTranscribeService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionTranscribeService(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Transfer\TransferClient createTransfer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionTransfer(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Translate\TranslateClient createTranslate(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionTranslate(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WAFV2\WAFV2Client createWAFV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWAFV2(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Waf\WafClient createWaf(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWaf(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WafRegional\WafRegionalClient createWafRegional(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWafRegional(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WorkDocs\WorkDocsClient createWorkDocs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWorkDocs(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WorkLink\WorkLinkClient createWorkLink(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWorkLink(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WorkMail\WorkMailClient createWorkMail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWorkMail(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WorkMailMessageFlow\WorkMailMessageFlowClient createWorkMailMessageFlow(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWorkMailMessageFlow(array $args = [])
 * @method \MediaCloud\Vendor\Aws\WorkSpaces\WorkSpacesClient createWorkSpaces(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionWorkSpaces(array $args = [])
 * @method \MediaCloud\Vendor\Aws\XRay\XRayClient createXRay(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionXRay(array $args = [])
 * @method \MediaCloud\Vendor\Aws\imagebuilder\imagebuilderClient createimagebuilder(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionimagebuilder(array $args = [])
 * @method \MediaCloud\Vendor\Aws\kendra\kendraClient createkendra(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionkendra(array $args = [])
 * @method \MediaCloud\Vendor\Aws\signer\signerClient createsigner(array $args = [])
 * @method \MediaCloud\Vendor\Aws\MultiRegionClient createMultiRegionsigner(array $args = [])
 */
class Sdk
{
    const VERSION = '3.150.3';

    /** @var array Arguments for creating clients */
    private $args;

    /**
     * Constructs a new SDK object with an associative array of default
     * client settings.
     *
     * @param array $args
     *
     * @throws \InvalidArgumentException
     * @see MediaCloud\Vendor\Aws\AwsClient::__construct for a list of available options.
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;

        if (!isset($args['handler']) && !isset($args['http_handler'])) {
            $this->args['http_handler'] = default_http_handler();
        }
    }

    public function __call($name, array $args)
    {
        $args = isset($args[0]) ? $args[0] : [];
        if (strpos($name, 'createMultiRegion') === 0) {
            return $this->createMultiRegionClient(substr($name, 17), $args);
        }

        if (strpos($name, 'create') === 0) {
            return $this->createClient(substr($name, 6), $args);
        }

        throw new \BadMethodCallException("Unknown method: {$name}.");
    }

    /**
     * Get a client by name using an array of constructor options.
     *
     * @param string $name Service name or namespace (e.g., DynamoDb, s3).
     * @param array  $args Arguments to configure the client.
     *
     * @return AwsClientInterface
     * @throws \InvalidArgumentException if any required options are missing or
     *                                   the service is not supported.
     * @see MediaCloud\Vendor\Aws\AwsClient::__construct for a list of available options for args.
     */
    public function createClient($name, array $args = [])
    {
        // Get information about the service from the manifest file.
        $service = manifest($name);
        $namespace = $service['namespace'];

        // Instantiate the client class.
        $client = "MediaCloud\\Vendor\\Aws\\{$namespace}\\{$namespace}Client";
        return new $client($this->mergeArgs($namespace, $service, $args));
    }

    public function createMultiRegionClient($name, array $args = [])
    {
        // Get information about the service from the manifest file.
        $service = manifest($name);
        $namespace = $service['namespace'];

        $klass = "MediaCloud\\Vendor\\Aws\\{$namespace}\\{$namespace}MultiRegionClient";
        $klass = class_exists($klass) ? $klass : 'MediaCloud\\Vendor\\Aws\\MultiRegionClient';

        return new $klass($this->mergeArgs($namespace, $service, $args));
    }

    /**
     * Clone existing SDK instance with ability to pass an associative array
     * of extra client settings.
     *
     * @param array $args
     *
     * @return self
     */
    public function copy(array $args = [])
    {
        return new self($args + $this->args);
    }

    private function mergeArgs($namespace, array $manifest, array $args = [])
    {
        // Merge provided args with stored, service-specific args.
        if (isset($this->args[$namespace])) {
            $args += $this->args[$namespace];
        }

        // Provide the endpoint prefix in the args.
        if (!isset($args['service'])) {
            $args['service'] = $manifest['endpoint'];
        }

        return $args + $this->args;
    }

    /**
     * Determine the endpoint prefix from a client namespace.
     *
     * @param string $name Namespace name
     *
     * @return string
     * @internal
     * @deprecated Use the `\Aws\manifest()` function instead.
     */
    public static function getEndpointPrefix($name)
    {
        return manifest($name)['endpoint'];
    }
}
