<?php


namespace MediaCloud\Plugin\Tasks;


class MultiReporter implements ITaskReporter {
	/** @var ITaskReporter[] */
	protected $reporters = [];

	/**
	 * MultiReporter constructor.
	 *
	 * @param ITaskReporter[]|null $reporters
	 */
	public function __construct(?array $reporters = null) {
		if (!empty($reporters)) {
			$this->reporters = $reporters;
		}
	}

	public function addReporter(ITaskReporter $reporter) {
		$this->reporters[] = $reporter;
	}

	/**
	 * @inheritDoc
	 */
	public function open() {
		foreach($this->reporters as $reporter) {
			$reporter->open();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function add(array $data) {
		foreach($this->reporters as $reporter) {
			$reporter->add($data);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function close() {
		foreach($this->reporters as $reporter) {
			$reporter->close();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function headerFields(): array {
		return (count($this->reporters) > 0) ? $this->reporters[0]->headerFields() : [];
	}
}