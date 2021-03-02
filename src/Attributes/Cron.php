<?php

namespace Yauhenko\CronBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Cron {

	public function __construct(
		public string $command,
		public ?string $time = null,
		public string $minute = '*',
		public string $hour = '*',
		public string $day = '*',
		public string $month = '*',
		public string $week = '*',
		public bool $hourly = false,
		public bool $daily = false,
		public bool $weekly = false,
		public bool $monthly = false,
	) {}

	public function getCmd(string $consolePath): string {
		if($this->time) {
			$time = $this->time;
		} elseif($this->hourly) {
			$time = '@hourly';
		} elseif($this->daily) {
			$time = '@daily';
		} elseif($this->weekly) {
			$time = '@weekly';
		} elseif($this->monthly) {
			$time = '@monthly';
		} else {
			$time = "{$this->minute} {$this->hour} {$this->day} {$this->month} {$this->week}";
		}
		$cmd = realpath($consolePath) . ' ' . $this->command;
		return $time . ' ' . $cmd;
	}

}
