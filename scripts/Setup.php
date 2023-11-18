<?php

declare(strict_types=1);

$name = $argv[1];
if (!$name || false === strpos($name, '/')) {
    echo 'Usage: php scripts/Setup.php vendor/package' . PHP_EOL;
    exit(1);
}

[$vendor, $package] = explode('/', $name);
$composer  = json_decode(file_get_contents(__DIR__ . '/../skeleton/composer.json'));

$title = implode(' ', array_map(fn (string $s) => ucfirst($s), explode(' ', str_replace(['-', '_'], ' ', $vendor . ' ' . $package))));
$namespace = implode('', array_map(fn (string $s) => ucfirst($s), explode('-', $vendor))) . '\\' . implode('', array_map(fn (string $s) => ucfirst($s), explode('-', $package)));

// setup composer
$composer->name = $name;
$composer->title ??= $title;
$composer->description ??= $title . ' - Library';
$composer->autoload->{'psr-4'} = (object) [$namespace . '\\' => 'src/'];
$composer->{'autoload-dev'}->{'psr-4'} = (object) [$namespace . '\\Tests\\' => 'tests/'];
$composer->type = 'library';

file_put_contents(__DIR__ . '/../skeleton/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// setup namespace in files
$files = [
    dirname(__DIR__) . '/skeleton/src/Kernel.php',
    dirname(__DIR__) . '/skeleton/bin/console',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace('$NAMESPACE', $namespace, $content);
    file_put_contents($file, $content);
}
