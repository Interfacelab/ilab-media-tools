<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace MediaCloud\Plugin\Tasks;

use MediaCloud\Vendor\Cron\CronExpression;

/**
 * @method self everyMinute()      Run task every minute.
 * @method self everyFiveMinutes() Run task every five minutes.
 * @method self everyHour()        Run task every hour.
 * @method self everyDay()         Run task every day.
 * @method self everyMonth()       Run task every month.
 */
class RecurringTaskScheduler {
	private $taskType;
	private $options;
	private $selection;
	private $cronExpression = '* * * * *';

	private $fieldsPosition = [
		'minute' => 1,
		'hour' => 2,
		'day' => 3,
		'month' => 4,
		'week' => 5,
	];

	//region Init

	public function __construct($taskType, $options, $selection) {
		$this->taskType = $taskType;
		$this->options = $options;
		$this->selection = $selection;
	}

	//endregion

	//region Dynamic

	public function __call($name, $arguments) {
		preg_match('/^every([A-Z][a-zA-Z]+)?(Minute|Hour|Day|Month)s?$/', $name, $matches);


		if (!count($matches) || ('Zero' === $matches[1])) {
			throw new \BadMethodCallException("Method '{$name}' is not supported.");
		}

		$amount = !empty($matches[1]) ? $this->wordToNumber($this->splitCamel($matches[1])) : 1;
		if (!$amount) {
			throw new \BadMethodCallException();
		}

		return $this->every(mb_strtolower($matches[2]), $amount);
	}

	//endregion


	//region Cron

	/**
	 * Splice the given value into the given position of the expression.
	 *
	 * @param int    $position
	 * @param string $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	protected function spliceIntoPosition($position, $value)  {
		$segments = explode(' ', $this->cronExpression);
		$segments[$position - 1] = $value;

		return $this->cron(implode(' ', $segments));
	}

	/**
	 * The Cron expression representing the event's frequency.
	 *
	 * @param string $expression
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function cron($expression) {
		/** @var array $parts */
		$parts = preg_split('/\s/', $expression, -1, PREG_SPLIT_NO_EMPTY);
		if (count($parts) > 5) {
			throw new \Exception("Expression '{$expression}' has more than five parts and this is not allowed.");
		}

		$this->cronExpression = $expression;
		return $this;
	}

	//endregion

	//region Scheduling
	/**
	 * Schedule the event to run hourly.
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function hourly() {
		return $this->cron('0 * * * *');
	}

	/**
	 * Schedule the event to run daily.
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function daily() {
		return $this->cron('0 0 * * *');
	}

	/**
	 * Schedule the event to run on a certain date.
	 *
	 * @param string $date
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function on($date)  {
		$parsedDate = date_parse($date);
		$segments = array_intersect_key($parsedDate, $this->fieldsPosition);

		foreach ($segments as $key => $value) {
			if (false !== $value) {
				$this->spliceIntoPosition($this->fieldsPosition[$key], (string) $value);
			}
		}

		return $this;
	}

	/**
	 * Schedule the command at a given time.
	 *
	 * @param string $time
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function at($time) {
		return $this->dailyAt($time);
	}

	/**
	 * Schedule the event to run daily at a given time (10:00, 19:30, etc).
	 *
	 * @param string $time
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function dailyAt($time) {
		$segments = explode(':', $time);
		$firstSegment = (int)$segments[0];
		$secondSegment = count($segments) > 1 ? (int) $segments[1] : '0';

		return $this
			->spliceIntoPosition(2, $firstSegment)
			->spliceIntoPosition(1, $secondSegment)
		;
	}

	/**
	 * Schedule the event to run twice daily.
	 *
	 * @param int $first
	 * @param int $second
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function twiceDaily($first = 1, $second = 13) {
		$hours = $first . ',' . $second;

		return $this
			->spliceIntoPosition(1, '0')
			->spliceIntoPosition(2, $hours)
			;
	}

	/**
	 * Schedule the event to run only on weekdays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function weekdays() {
		return $this->spliceIntoPosition(5, '1-5');
	}

	/**
	 * Schedule the event to run only on Mondays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function mondays() {
		return $this->days(1);
	}

	/**
	 * Schedule the event to run only on Tuesdays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function tuesdays() {
		return $this->days(2);
	}

	/**
	 * Schedule the event to run only on Wednesdays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function wednesdays() {
		return $this->days(3);
	}

	/**
	 * Schedule the event to run only on Thursdays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function thursdays() {
		return $this->days(4);
	}

	/**
	 * Schedule the event to run only on Fridays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function fridays() {
		return $this->days(5);
	}

	/**
	 * Schedule the event to run only on Saturdays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function saturdays() {
		return $this->days(6);
	}

	/**
	 * Schedule the event to run only on Sundays.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function sundays() {
		return $this->days(0);
	}

	/**
	 * Schedule the event to run weekly.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function weekly() {
		return $this->cron('0 0 * * 0');
	}

	/**
	 * Schedule the event to run weekly on a given day and time.
	 *
	 * @param int|string $day
	 * @param string $time
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function weeklyOn($day, $time = '0:0') {
		$this->dailyAt($time);

		return $this->spliceIntoPosition(5, (string) $day);
	}

	/**
	 * Schedule the event to run monthly.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function monthly() {
		return $this->cron('0 0 1 * *');
	}

	/**
	 * Schedule the event to run quarterly.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function quarterly() {
		return $this->cron('0 0 1 */3 *');
	}

	/**
	 * Schedule the event to run yearly.
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function yearly() {
		return $this->cron('0 0 1 1 *');
	}

	/**
	 * Set the days of the week the command should run on.
	 *
	 * @param mixed $days
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function days($days) {
		$days =is_array($days) ? $days : func_get_args();

		return $this->spliceIntoPosition(5, implode(',', $days));
	}

	/**
	 * Set hour for the cron job.
	 *
	 * @param mixed $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function hour($value) {
		$value =is_array($value) ? $value : func_get_args();

		return $this->spliceIntoPosition(2, implode(',', $value));
	}

	/**
	 * Set minute for the cron job.
	 *
	 * @param mixed $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function minute($value) {
		$value =is_array($value) ? $value : func_get_args();

		return $this->spliceIntoPosition(1, implode(',', $value));
	}

	/**
	 * Set hour for the cron job.
	 *
	 * @param mixed $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function dayOfMonth($value) {
		$value =is_array($value) ? $value : func_get_args();

		return $this->spliceIntoPosition(3, implode(',', $value));
	}

	/**
	 * Set hour for the cron job.
	 *
	 * @param mixed $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function month($value) {
		$value =is_array($value) ? $value : func_get_args();

		return $this->spliceIntoPosition(4, implode(',', $value));
	}

	/**
	 * Set hour for the cron job.
	 *
	 * @param mixed $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function dayOfWeek($value) {
		$value =is_array($value) ? $value : func_get_args();

		return $this->spliceIntoPosition(5, implode(',', $value));
	}

	/**
	 * Another way to the frequency of the cron job.
	 *
	 * @param null $unit
	 * @param null $value
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	public function every($unit = null, $value = null) {
		if (null === $unit || !isset($this->fieldsPosition[$unit])) {
			return $this;
		}

		$value = (1 === (int) $value) ? '*' : '*/' . $value;

		return $this->spliceIntoPosition($this->fieldsPosition[$unit], $value)
			->applyMask($unit);
	}

	//endregion

	//region Saving
	public function save() {
		$schedule = new TaskSchedule();
		$schedule->taskType = $this->taskType;

		$cron = CronExpression::factory($this->cronExpression);
		$schedule->nextRun = $cron->getNextRunDate()->getTimestamp();

		$schedule->recurring = true;
		$schedule->schedule = $this->cronExpression;
		$schedule->options = $this->options;
		$schedule->selection = $this->selection;
		$schedule->save();

		return $schedule;
	}
	//endregion

	//region Utilities

	private function splitCamel($text) {
		$pattern = '/(?<=[a-z])(?=[A-Z])/x';
		/** @var array $segments */
		$segments = preg_split($pattern, $text);
		return mb_strtolower(implode(' ', $segments));
	}

	private function wordToNumber($text)
	{
		$data = strtr(
			$text,
			[
				'zero' => '0',
				'a' => '1',
				'one' => '1',
				'two' => '2',
				'three' => '3',
				'four' => '4',
				'five' => '5',
				'six' => '6',
				'seven' => '7',
				'eight' => '8',
				'nine' => '9',
				'ten' => '10',
				'eleven' => '11',
				'twelve' => '12',
				'thirteen' => '13',
				'fourteen' => '14',
				'fifteen' => '15',
				'sixteen' => '16',
				'seventeen' => '17',
				'eighteen' => '18',
				'nineteen' => '19',
				'twenty' => '20',
				'thirty' => '30',
				'forty' => '40',
				'fourty' => '40',
				'fifty' => '50',
				'sixty' => '60',
				'seventy' => '70',
				'eighty' => '80',
				'ninety' => '90',
				'hundred' => '100',
				'thousand' => '1000',
				'million' => '1000000',
				'billion' => '1000000000',
				'and' => '',
			]
		);

		/** @var array $matchedParts */
		$matchedParts =preg_split('/[\s-]+/', $data);
		// Coerce all tokens to numbers
		$parts =array_map('floatval', $matchedParts);

		$tmp = null;
		$sum = 0;
		$last = null;

		foreach ($parts as $part) {
			if (null !== $tmp) {
				if ($tmp > $part) {
					if ($last >= 1000) {
						$sum += $tmp;
						$tmp = $part;
					} else {
						$tmp += $part;
					}
				} else {
					$tmp *= $part;
				}
			} else {
				$tmp = $part;
			}

			$last = $part;
		}

		return $sum + $tmp;
	}

	/**
	 * Mask a cron expression.
	 *
	 * @param $unit
	 *
	 * @return RecurringTaskScheduler
	 * @throws \Exception
	 */
	protected function applyMask($unit) {
		$cron = explode(' ', $this->cronExpression);
		$mask = ['0', '0', '1', '1', '*', '*'];
		$fpos = $this->fieldsPosition[$unit] - 1;

		array_splice($cron, 0, $fpos, array_slice($mask, 0, $fpos));

		return $this->cron(implode(' ', $cron));
	}
	//endregion
}