#!/usr/bin/env php
<?php

function ask(string $question, string $default = ''): string
{
    $answer = readline($question . ($default ? " ({$default})" : null) . ': ');

    if (!$answer) {
        return $default;
    }

    return $answer;
}

function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question . ' (' . ($default ? 'Y/n' : 'y/N') . ')');

    if (!$answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function writeln(string $line): void
{
    echo $line . PHP_EOL;
}

function run(string $command): string
{
    return trim((string)shell_exec($command));
}

function slugify(string $subject): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

function title_case(string $subject): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject)));
}

function title_snake(string $subject, string $replace = '_'): string
{
    return str_replace(['-', '_'], $replace, $subject);
}

function replace_in_file(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

function remove_prefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return substr($content, strlen($prefix));
    }

    return $content;
}

function remove_readme_paragraphs(string $file): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

function determineSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i ' . basename(__FILE__) . ' | findstr /r /i /M /F:/ ":author :vendor :package VendorName skeleton migration_table_name vendor_name vendor_slug author@domain.com"'));
}

function replaceForAllOtherOSes(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i ":author|:vendor|:package|VendorName|skeleton|migration_table_name|vendor_name|vendor_slug|author@domain.com" --exclude-dir=vendor ./* ./.github/* | grep -v ' . basename(__FILE__)));
}

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$packageName = ask('Package name', $folderName);
$packageSlug = slugify($packageName);
$packageSlugWithoutPrefix = remove_prefix('laravel-', $packageSlug);

$className = title_case($packageName);
$className = ask('Class name', $className);
$variableName = lcfirst($className);
$description = ask('Package description', "This is my package {$packageSlug}");

writeln('------');
writeln('Author     : Luminarix Labs (luminarix, hello@luminarix.dev)');
writeln('Vendor     : Luminarix Labs (luminarix)');
writeln("Package    : {$packageSlug} <{$description}>");
writeln("Namespace  : Luminarix\\{$className}");
writeln("Class name : {$className}");
writeln('---');
writeln('Packages & Utilities used');
writeln('Laravel/Pint');
writeln('Larastan/PhpStan');
writeln('Dependabot');
writeln('Auto-Changelog');
writeln('------');

writeln('This script will replace the above values in all relevant files in the project directory.');

if (!confirm('Modify files?', true)) {
    exit(1);
}

$files = (str_starts_with(strtoupper(PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());

foreach ($files as $file) {
    replace_in_file($file, [
        ':package_name' => $packageName,
        ':package_slug' => $packageSlug,
        ':package_slug_without_prefix' => $packageSlugWithoutPrefix,
        'Skeleton' => $className,
        'skeleton' => $packageSlug,
        'migration_table_name' => title_snake($packageSlug),
        'variable' => $variableName,
        ':package_description' => $description,
    ]);

    match (true) {
        str_contains($file, determineSeparator('src/Skeleton.php')) => rename($file, determineSeparator('./src/' . $className . '.php')),
        str_contains($file, determineSeparator('src/SkeletonServiceProvider.php')) => rename($file, determineSeparator('./src/' . $className . 'ServiceProvider.php')),
        str_contains($file, determineSeparator('src/Facades/Skeleton.php')) => rename($file, determineSeparator('./src/Facades/' . $className . '.php')),
        str_contains($file, determineSeparator('src/Commands/SkeletonCommand.php')) => rename($file, determineSeparator('./src/Commands/' . $className . 'Command.php')),
        str_contains($file, determineSeparator('database/migrations/create_skeleton_table.php.stub')) => rename($file, determineSeparator('./database/migrations/create_' . title_snake($packageSlugWithoutPrefix) . '_table.php.stub')),
        str_contains($file, determineSeparator('config/skeleton.php')) => rename($file, determineSeparator('./config/' . $packageSlugWithoutPrefix . '.php')),
        str_contains($file, 'README.md') => remove_readme_paragraphs($file),
        default => [],
    };
}

confirm('Execute `composer install` and run tests?') && run('composer install && composer test');

confirm('Let this script delete itself?', true) && unlink(__FILE__);
