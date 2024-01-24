<?php

namespace Yauhenko\CronBundle\Service;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Yauhenko\CronBundle\Attributes\Cron;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CronService {

	protected string $crontab;
	protected string $id;
	protected string $dir;
	protected string $appNamespace;
	protected ParameterBagInterface $params;

	public function __construct(ParameterBagInterface $params) {
		$this->crontab = shell_exec('crontab -l') ?? '';
		$this->dir = $params->get('command_dir');
		$this->id = md5($this->dir);
		$this->appNamespace = $params->get('app_namespace');
		$this->params = $params;
	}

    /**
     * @throws ReflectionException
     */
    public function update(bool $removeOnly = false): void {
		$patternBegin = '#section:' . $this->id . ':begin';
		$patternEnd = '#section:' . $this->id . ':end';
		$crontab = $patternBegin . PHP_EOL . '#' . $this->dir . PHP_EOL;
		$cr = new ClassResolver;
		foreach($cr->getReflections($this->dir, $this->appNamespace) as $rc) {
			/** @var Cron $cron */
			foreach($this->getAttributes($rc, Cron::class) as $cron) {
				$crontab .= $cron->getCmd($this->params->get('console_path')) . PHP_EOL;
			}
		}
		$crontab .= $patternEnd;
		$patternBegin = preg_quote($patternBegin);
		$patternEnd = preg_quote($patternEnd);
		$this->crontab = (string)preg_replace("/{$patternBegin}.*{$patternEnd}/isU", '', $this->crontab);
		if($removeOnly) return;
		$this->crontab = trim(trim($this->crontab) . PHP_EOL . trim($crontab)) . PHP_EOL;
	}

	public function save(): void {
		$tmp = '/tmp/' . uniqid('cron');
		file_put_contents($tmp, $this->crontab);
		shell_exec('crontab ' . $tmp);
		unlink($tmp);
	}

	private function getAttributes(ReflectionClass|ReflectionProperty|ReflectionMethod $reflection, string $name): array {
		$attributes = $reflection->getAttributes($name);
		foreach($attributes as $k => $a) {
			$attributes[$k] = $a->newInstance();
		}
		return $attributes;
	}

}
