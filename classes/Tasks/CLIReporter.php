<?php


namespace MediaCloud\Plugin\Tasks;

class CLIReporter implements ITaskReporter {
	/** @var array  */
	protected $headerFields = [];

	protected $data = [];

	protected $format = 'table';

	/**
	 * CLITableReporter constructor.
	 *
	 * @param array $headerFields
	 * @param string $format
	 */
	public function __construct(array $headerFields, string $format = 'table') {
		$this->headerFields = $headerFields;
		$this->format = $format;
	}

	/**
	 * @inheritDoc
	 */
	public function open() {
	}

	/**
	 * @inheritDoc
	 */
	public function add(array $data) {
		$item = [];
		for($i = 0; $i < count($data); $i++) {
			if ($i >= count($this->headerFields)) {
				break;
			}

			$item[$this->headerFields[$i]] = $data[$i];
		}

		$this->data[] = $item;
	}

	/**
	 * @inheritDoc
	 */
	public function close() {
		\WP_CLI\Utils\format_items($this->format, $this->data, $this->headerFields);
	}

	/**
	 * @inheritDoc
	 */
	public function headerFields(): array {
		return $this->headerFields;
	}
}