<?php

/**
 * Test Validator
 * Runs unit tests to validate code functionality
 */

echo "🧪 PHPSchema Test Validator\n";
echo str_repeat("=", 50) . "\n\n";

$checks = [
    'tests' => false
];

$errors = [];

// Run Unit Tests
echo "🧪 Running Unit Tests...\n";
echo str_repeat("-", 40) . "\n";

$output = [];
$returnCode = 0;
exec("php tests/TestRunner.php", $output, $returnCode);

if ($returnCode === 0) {
    $checks['tests'] = true;
    echo "✅ All tests passed\n\n";
    
    // Show test output
    foreach ($output as $line) {
        echo $line . "\n";
    }
} else {
    $errors[] = "Unit tests failed";
    echo "❌ Tests failed\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    echo "\n";
}

// Coverage Report (basic file coverage)
echo "📊 Coverage Report...\n";
echo str_repeat("-", 40) . "\n";

$srcFiles = [];
$testFiles = [];

// Count source files
if (is_dir('src')) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('src', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $srcFiles[] = $file->getPathname();
        }
    }
}

// Count test files
if (is_dir('tests')) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('tests', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && str_contains($file->getFilename(), 'Test')) {
            $testFiles[] = $file->getPathname();
        }
    }
}

$srcCount = count($srcFiles);
$testCount = count($testFiles) - 1; // Subtract TestRunner.php
$coverage = $srcCount > 0 ? round(($testCount / $srcCount) * 100, 1) : 0;

echo "📊 File Coverage: {$coverage}% ({$testCount} test files for {$srcCount} source files)\n";
echo "📁 Source files: {$srcCount}\n";
echo "🧪 Test files: {$testCount}\n\n";

// Final Summary
echo str_repeat("=", 50) . "\n";
echo "📊 Test Validation Report\n";
echo str_repeat("=", 50) . "\n";

echo "🧪 Tests: " . ($checks['tests'] ? "✅ Pass" : "❌ Fail") . "\n";
echo "📊 Coverage: {$coverage}%\n";

if (!empty($errors)) {
    echo "\n💥 Issues Found:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
    echo "\n❌ Test validation failed!\n";
    echo "   Please fix the failing tests before proceeding.\n";
    exit(1);
} else {
    echo "\n🎉 All tests passed!\n";
    echo "   Code is ready for deployment.\n";
    exit(0);
}