<?php
namespace ILAB_Aws\CodeCommit;

use ILAB_Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CodeCommit** service.
 *
 * @method \ILAB_Aws\Result batchGetRepositories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetRepositoriesAsync(array $args = [])
 * @method \ILAB_Aws\Result createBranch(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBranchAsync(array $args = [])
 * @method \ILAB_Aws\Result createRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \ILAB_Aws\Result deleteRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \ILAB_Aws\Result getBranch(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBranchAsync(array $args = [])
 * @method \ILAB_Aws\Result getCommit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getCommitAsync(array $args = [])
 * @method \ILAB_Aws\Result getRepository(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRepositoryAsync(array $args = [])
 * @method \ILAB_Aws\Result getRepositoryTriggers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRepositoryTriggersAsync(array $args = [])
 * @method \ILAB_Aws\Result listBranches(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBranchesAsync(array $args = [])
 * @method \ILAB_Aws\Result listRepositories(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRepositoriesAsync(array $args = [])
 * @method \ILAB_Aws\Result putRepositoryTriggers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putRepositoryTriggersAsync(array $args = [])
 * @method \ILAB_Aws\Result testRepositoryTriggers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise testRepositoryTriggersAsync(array $args = [])
 * @method \ILAB_Aws\Result updateDefaultBranch(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDefaultBranchAsync(array $args = [])
 * @method \ILAB_Aws\Result updateRepositoryDescription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRepositoryDescriptionAsync(array $args = [])
 * @method \ILAB_Aws\Result updateRepositoryName(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRepositoryNameAsync(array $args = [])
 */
class CodeCommitClient extends AwsClient {}
