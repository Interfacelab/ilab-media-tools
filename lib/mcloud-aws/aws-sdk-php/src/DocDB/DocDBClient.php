<?php

namespace MediaCloud\Vendor\Aws\DocDB;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\PresignUrlMiddleware;

/**
 * This client is used to interact with the **Amazon DocumentDB with MongoDB compatibility** service.
 * @method \MediaCloud\Vendor\Aws\Result addSourceIdentifierToSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addSourceIdentifierToSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addTagsToResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result applyPendingMaintenanceAction(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise applyPendingMaintenanceActionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyDBClusterParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBClusterParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyDBClusterSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBClusterSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBClusterParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBClusterSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createGlobalCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGlobalClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBClusterParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBClusterSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteGlobalCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteGlobalClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeCertificates(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCertificatesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterParameterGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterParameterGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterParameters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterParametersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterSnapshotAttributes(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterSnapshotAttributesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterSnapshots(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterSnapshotsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClustersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBEngineVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBEngineVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBSubnetGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBSubnetGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEngineDefaultClusterParameters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEngineDefaultClusterParametersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventCategories(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventSubscriptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeGlobalClusters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeGlobalClustersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOrderableDBInstanceOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOrderableDBInstanceOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describePendingMaintenanceActions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePendingMaintenanceActionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result failoverDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise failoverDBClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBClusterParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBClusterSnapshotAttribute(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterSnapshotAttributeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyGlobalCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyGlobalClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rebootDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rebootDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeFromGlobalCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeFromGlobalClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeSourceIdentifierFromSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeSourceIdentifierFromSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTagsFromResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result resetDBClusterParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise resetDBClusterParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreDBClusterFromSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBClusterFromSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreDBClusterToPointInTime(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBClusterToPointInTimeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result startDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDBClusterAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result stopDBCluster(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopDBClusterAsync(array $args = [])
 */
class DocDBClient extends AwsClient {
    public function __construct(array $args)
    {
        $args['with_resolved'] = function (array $args) {
            $this->getHandlerList()->appendInit(
                PresignUrlMiddleware::wrap(
                    $this,
                    $args['endpoint_provider'],
                    [
                        'operations' => [
                            'CopyDBClusterSnapshot',
                            'CreateDBCluster',
                        ],
                        'service' => 'rds',
                        'presign_param' => 'PreSignedUrl',
                        'require_different_region' => true,
                        'extra_query_params' => [
                            'CopyDBClusterSnapshot' => ['DestinationRegion'],
                            'CreateDBCluster' => ['DestinationRegion'],
                        ]
                    ]
                ),
                'rds.presigner'
            );
        };
        parent::__construct($args);
    }
}
