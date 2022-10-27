<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\VarDumper\Cloner;
use MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\Caster;
use MediaCloud\Vendor\Symfony\Component\VarDumper\Exception\ThrowingCasterException;

/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = [
        '__PHP_Incomplete_Class' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],

        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\CutStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\CutArrayStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castCutArray'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ConstStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\EnumStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'castEnum'],

        'Fiber' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\FiberCaster', 'castFiber'],

        'Closure' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionAttribute' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castAttribute'],
        'ReflectionGenerator' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionClassConstant' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClassConstant'],
        'ReflectionFunctionAbstract' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionReference' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReference'],
        'ReflectionExtension' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],

        'Doctrine\Common\Persistence\ObjectManager' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],
        'Doctrine\Persistence\ObjectManager' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'DOMException' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNameList' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMImplementation' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNode' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNode'],
        'DOMNameSpaceNode' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'],
        'DOMDocument' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocument'],
        'DOMNodeList' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNamedNodeMap' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMCharacterData' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'DOMAttr' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castAttr'],
        'DOMElement' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castElement'],
        'DOMText' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castText'],
        'DOMTypeinfo' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castTypeinfo'],
        'DOMDomError' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDomError'],
        'DOMLocator' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLocator'],
        'DOMDocumentType' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'DOMNotation' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNotation'],
        'DOMEntity' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castEntity'],
        'DOMProcessingInstruction' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'DOMXPath' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DOMCaster', 'castXPath'],

        'XMLReader' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],

        'ErrorException' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Bridge\Monolog\Logger' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'MediaCloud\Vendor\Symfony\Component\DependencyInjection\ContainerInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'MediaCloud\Vendor\Symfony\Component\EventDispatcher\EventDispatcherInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\AmpHttpClient' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\CurlHttpClient' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\NativeHttpClient' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\Response\AmpResponse' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\Response\CurlResponse' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'MediaCloud\Vendor\Symfony\Component\HttpClient\Response\NativeResponse' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'],
        'MediaCloud\Vendor\Symfony\Component\HttpFoundation\Request' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'MediaCloud\Vendor\Symfony\Component\Uid\Ulid' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUlid'],
        'MediaCloud\Vendor\Symfony\Component\Uid\Uuid' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUuid'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Exception\ThrowingCasterException' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\TraceStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\FrameStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Cloner\AbstractCloner' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'MediaCloud\Vendor\Symfony\Component\ErrorHandler\Exception\SilencedErrorContext' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],

        'Imagine\Image\ImageInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ImagineCaster', 'castImage'],

        'Ramsey\Uuid\UuidInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\UuidCaster', 'castRamseyUuid'],

        'ProxyManager\Proxy\ProxyInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\MockObject' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'PHPUnit\Framework\MockObject\Stub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'],

        'PDO' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdoStatement'],

        'AMQPConnection' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castEnvelope'],

        'ArrayObject' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplHeap' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'WeakReference' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakReference'],

        'Redis' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisCluster'],

        'DateTimeInterface' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DateCaster', 'castPeriod'],

        'GMP' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\GmpCaster', 'castGmp'],

        'MessageFormatter' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],

        'Memcached' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\MemcachedCaster', 'castMemcached'],

        'Ds\Collection' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castCollection'],
        'Ds\Map' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castMap'],
        'Ds\Pair' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castPair'],
        'MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DsPairStub' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\DsCaster', 'castPairStub'],

        'mysqli_driver' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\MysqliCaster', 'castMysqliDriver'],

        'CurlHandle' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'],
        ':curl' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'],

        ':dba' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'],

        'GdImage' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'],
        ':gd' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'],

        ':mysql link' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castMysqlLink'],
        ':pgsql large object' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        ':pgsql link' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql link persistent' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql result' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castResult'],
        ':process' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],

        'OpenSSLCertificate' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],
        ':OpenSSL X.509' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],

        ':persistent stream' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStreamContext'],

        'XmlParser' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],
        ':xml' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'],

        'RdKafka' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castRdKafka'],
        'RdKafka\Conf' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castConf'],
        'RdKafka\KafkaConsumer' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castKafkaConsumer'],
        'RdKafka\Metadata\Broker' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castBrokerMetadata'],
        'RdKafka\Metadata\Collection' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castCollectionMetadata'],
        'RdKafka\Metadata\Partition' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castPartitionMetadata'],
        'RdKafka\Metadata\Topic' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicMetadata'],
        'RdKafka\Message' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castMessage'],
        'RdKafka\Topic' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopic'],
        'RdKafka\TopicPartition' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicPartition'],
        'RdKafka\TopicConf' => ['MediaCloud\Vendor\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicConf'],
    ];

    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;

    /**
     * @var array<string, list<callable>>
     */
    private $casters = [];

    /**
     * @var callable|null
     */
    private $prevErrorHandler;

    private $classInfo = [];
    private $filter = 0;

    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }

    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }

    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     */
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;
    }

    /**
     * Sets the maximum cloned length for strings.
     */
    public function setMaxString(int $maxString)
    {
        $this->maxString = $maxString;
    }

    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     */
    public function setMinDepth(int $minDepth)
    {
        $this->minDepth = $minDepth;
    }

    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data
     */
    public function cloneVar($var, int $filter = 0)
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }

            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }

            return false;
        });
        $this->filter = $filter;

        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }

    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array
     */
    abstract protected function doClone($var);

    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castObject(Stub $stub, bool $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;

        if (\PHP_VERSION_ID < 80000 ? "\0" === ($class[15] ?? null) : str_contains($class, "@anonymous\0")) {
            $stub->class = get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = method_exists($class, '__debugInfo');

            foreach (class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';

            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(Stub::class) ? [] : [
                'file' => $r->getFileName(),
                'line' => $r->getStartLine(),
            ];

            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }

        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);

        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }

    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castResource(Stub $stub, bool $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;

        try {
            if (!empty($this->casters[':'.$type])) {
                foreach ($this->casters[':'.$type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }
}
