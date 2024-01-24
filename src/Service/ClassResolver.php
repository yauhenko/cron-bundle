<?php

namespace Yauhenko\CronBundle\Service;

use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ReflectionException;

class ClassResolver {

    /**
     * @return ReflectionClass[]
     * @throws ReflectionException
     */
	public function getReflections(string $dir, string $appNamespace = 'App'): array {
		$result = $this->getNames($dir, $appNamespace);
		array_walk($result, function(string &$class) {
			$class = new ReflectionClass($class);
		});
		return $result;
	}

	public function getNames(string $dir, string $appNamespace = 'App'): array {
		$result = [];
		$it = new RecursiveDirectoryIterator($dir);
		foreach(new RecursiveIteratorIterator($it) as $file) {
			if(preg_match('/\.php$/', $file)) {
				$class = $appNamespace . str_replace(['.php', '/'], ['', '\\'], preg_replace('/^.+\/src\//', '/', $file));
				if(!class_exists($class)) {
					$data = file_get_contents($file);
					preg_match('/namespace\s+(.+);/isU', $data, $ns);
					$ns = trim($ns[1] ?? '');
					$class = ($ns ? $ns . '\\' : '') . pathinfo($file, PATHINFO_FILENAME);
					if(!class_exists($class)) continue;
				}
				$result[] = $class;
			}
		}
		return $result;
	}

}
