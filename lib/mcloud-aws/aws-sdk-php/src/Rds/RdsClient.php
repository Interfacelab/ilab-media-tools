<?php

namespace MediaCloud\Vendor\Aws\Rds;
use MediaCloud\Vendor\Aws\AwsClient;
use MediaCloud\Vendor\Aws\Api\Service;
use MediaCloud\Vendor\Aws\Api\DocModel;
use MediaCloud\Vendor\Aws\Api\ApiProvider;
use MediaCloud\Vendor\Aws\PresignUrlMiddleware;

/**
 * This client is used to interact with the **Amazon Relational Database Service (Amazon RDS)**.
 *
 * @method \MediaCloud\Vendor\Aws\Result addSourceIdentifierToSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addSourceIdentifierToSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addTagsToResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result authorizeDBSecurityGroupIngress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise authorizeDBSecurityGroupIngressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyDBParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyDBSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result copyOptionGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyOptionGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBInstanceReadReplica(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBInstanceReadReplicaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBSecurityGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBSecurityGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result createOptionGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createOptionGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBSecurityGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBSecurityGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result deleteOptionGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteOptionGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBEngineVersions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBEngineVersionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBLogFiles(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBLogFilesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBParameterGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBParameterGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBParameters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBParametersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBSecurityGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBSecurityGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBSnapshots(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBSnapshotsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeDBSubnetGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBSubnetGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEngineDefaultParameters(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEngineDefaultParametersAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventCategories(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEventSubscriptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeEvents(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOptionGroupOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOptionGroupOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOptionGroups(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOptionGroupsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeOrderableDBInstanceOptions(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeOrderableDBInstanceOptionsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReservedDBInstances(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReservedDBInstancesAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result describeReservedDBInstancesOfferings(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeReservedDBInstancesOfferingsAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result downloadDBLogFilePortion(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise downloadDBLogFilePortionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result listTagsForResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyDBSubnetGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBSubnetGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyEventSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result modifyOptionGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyOptionGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result promoteReadReplica(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise promoteReadReplicaAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result purchaseReservedDBInstancesOffering(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise purchaseReservedDBInstancesOfferingAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result rebootDBInstance(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rebootDBInstanceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeSourceIdentifierFromSubscription(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeSourceIdentifierFromSubscriptionAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result removeTagsFromResource(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result resetDBParameterGroup(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise resetDBParameterGroupAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreDBInstanceFromDBSnapshot(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBInstanceFromDBSnapshotAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result restoreDBInstanceToPointInTime(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBInstanceToPointInTimeAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result revokeDBSecurityGroupIngress(array $args = [])
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise revokeDBSecurityGroupIngressAsync(array $args = [])
 * @method \MediaCloud\Vendor\Aws\Result addRoleToDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addRoleToDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result addRoleToDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise addRoleToDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result applyPendingMaintenanceAction(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise applyPendingMaintenanceActionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result backtrackDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise backtrackDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result cancelExportTask(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise cancelExportTaskAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result copyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result copyDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise copyDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createCustomAvailabilityZone(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCustomAvailabilityZoneAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createCustomDBEngineVersion(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createCustomDBEngineVersionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createDBProxyEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createDBProxyEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result createGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise createGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteCustomAvailabilityZone(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCustomAvailabilityZoneAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteCustomDBEngineVersion(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteCustomDBEngineVersionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBInstanceAutomatedBackup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBInstanceAutomatedBackupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteDBProxyEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteDBProxyEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deleteInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deleteInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result deregisterDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise deregisterDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeAccountAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeCertificates(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCertificatesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeCustomAvailabilityZones(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeCustomAvailabilityZonesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterBacktracks(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterBacktracksAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterEndpoints(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterEndpointsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterParameterGroups(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterParameterGroupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusterSnapshots(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClusterSnapshotsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBClusters(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBClustersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBInstanceAutomatedBackups(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBInstanceAutomatedBackupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBProxies(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBProxiesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBProxyEndpoints(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBProxyEndpointsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBProxyTargetGroups(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBProxyTargetGroupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeDBSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeDBSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeEngineDefaultClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeEngineDefaultClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeExportTasks(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeExportTasksAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeGlobalClusters(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeGlobalClustersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describePendingMaintenanceActions(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describePendingMaintenanceActionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeSourceRegions(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeSourceRegionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result describeValidDBInstanceModifications(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise describeValidDBInstanceModificationsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result failoverDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise failoverDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result failoverGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise failoverGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result importInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise importInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyCertificates(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyCertificatesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyCurrentDBClusterCapacity(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyCurrentDBClusterCapacityAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyCustomDBEngineVersion(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyCustomDBEngineVersionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBClusterSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBClusterSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBProxyEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBProxyEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBProxyTargetGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBProxyTargetGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyDBSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyDBSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result modifyGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise modifyGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result promoteReadReplicaDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise promoteReadReplicaDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result rebootDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise rebootDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result registerDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise registerDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result removeFromGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeFromGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result removeRoleFromDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeRoleFromDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result removeRoleFromDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise removeRoleFromDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result resetDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise resetDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result restoreDBClusterFromS3(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBClusterFromS3Async(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result restoreDBClusterFromSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBClusterFromSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result restoreDBClusterToPointInTime(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBClusterToPointInTimeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result restoreDBInstanceFromS3(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise restoreDBInstanceFromS3Async(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result startActivityStream(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startActivityStreamAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result startDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result startDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result startDBInstanceAutomatedBackupsReplication(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startDBInstanceAutomatedBackupsReplicationAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result startExportTask(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise startExportTaskAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result stopActivityStream(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopActivityStreamAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result stopDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result stopDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\Aws\Result stopDBInstanceAutomatedBackupsReplication(array $args = []) (supported in versions 2014-10-31)
 * @method \MediaCloud\Vendor\GuzzleHttp\Promise\Promise stopDBInstanceAutomatedBackupsReplicationAsync(array $args = []) (supported in versions 2014-10-31)
 */
class RdsClient extends AwsClient
{
    public function __construct(array $args)
    {
        $args['with_resolved'] = function (array $args) {
            $this->getHandlerList()->appendInit(
                PresignUrlMiddleware::wrap(
                    $this,
                    $args['endpoint_provider'],
                    [
                        'operations' => [
                            'CopyDBSnapshot',
                            'CreateDBInstanceReadReplica',
                            'CopyDBClusterSnapshot',
                            'CreateDBCluster',
                            'StartDBInstanceAutomatedBackupsReplication'
                        ],
                        'service' => 'rds',
                        'presign_param' => 'PreSignedUrl',
                        'require_different_region' => true,
                    ]
                ),
                'rds.presigner'
            );
        };

        parent::__construct($args);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        // Add the SourceRegion parameter
        $docs['shapes']['SourceRegion']['base'] = 'A required parameter that indicates '
            . 'the region that the DB snapshot will be copied from.';
        $api['shapes']['SourceRegion'] = ['type' => 'string'];
        $api['shapes']['CopyDBSnapshotMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];
        $api['shapes']['CreateDBInstanceReadReplicaMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];

        // Add the DestinationRegion parameter
        $docs['shapes']['DestinationRegion']['base']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';
        $api['shapes']['DestinationRegion'] = ['type' => 'string'];
        $api['shapes']['CopyDBSnapshotMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];
        $api['shapes']['CreateDBInstanceReadReplicaMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];

        // Several parameters in presign APIs are optional.
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$PreSignedUrl']
            = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf.</div>';
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$DestinationRegion']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';

        // Several parameters in presign APIs are optional.
        $docs['shapes']['String']['refs']['CreateDBInstanceReadReplicaMessage$PreSignedUrl']
            = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf.</div>';
        $docs['shapes']['String']['refs']['CreateDBInstanceReadReplicaMessage$DestinationRegion']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';

        if ($api['metadata']['apiVersion'] != '2014-09-01') {
            $api['shapes']['CopyDBClusterSnapshotMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];
            $api['shapes']['CreateDBClusterMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];

            $api['shapes']['CopyDBClusterSnapshotMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];
            $api['shapes']['CreateDBClusterMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];

            // Several parameters in presign APIs are optional.
            $docs['shapes']['String']['refs']['CopyDBClusterSnapshotMessage$PreSignedUrl']
                = '<div class="alert alert-info">The SDK will compute this value '
                . 'for you on your behalf.</div>';
            $docs['shapes']['String']['refs']['CopyDBClusterSnapshotMessage$DestinationRegion']
                = '<div class="alert alert-info">The SDK will populate this '
                . 'parameter on your behalf using the configured region value of '
                . 'the client.</div>';

            // Several parameters in presign APIs are optional.
            $docs['shapes']['String']['refs']['CreateDBClusterMessage$PreSignedUrl']
                = '<div class="alert alert-info">The SDK will compute this value '
                . 'for you on your behalf.</div>';
            $docs['shapes']['String']['refs']['CreateDBClusterMessage$DestinationRegion']
                = '<div class="alert alert-info">The SDK will populate this '
                . 'parameter on your behalf using the configured region value of '
                . 'the client.</div>';
        }

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
