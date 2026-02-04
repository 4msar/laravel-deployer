<?php

use App\Commands\DeployCommand;

test('deploy command exists', function () {
    expect(class_exists(DeployCommand::class))->toBeTrue();
});

test('deploy command can be instantiated', function () {
    $command = new DeployCommand();
    expect($command)->toBeInstanceOf(DeployCommand::class);
});

test('deploy command has correct signature using reflection', function () {
    $command = new DeployCommand();
    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('signature');
    $property->setAccessible(true);

    $signature = $property->getValue($command);

    expect($signature)
        ->toContain('deploy')
        ->toContain('--repo')
        ->toContain('--app-name');
});

test('deploy command has description using reflection', function () {
    $command = new DeployCommand();
    $reflection = new ReflectionClass($command);
    $property = $reflection->getProperty('description');
    $property->setAccessible(true);

    $description = $property->getValue($command);

    expect($description)
        ->not->toBeEmpty()
        ->toContain('Deploy');
});
