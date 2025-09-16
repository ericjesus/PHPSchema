<?php

/**
 * Simple Test Runner for PHPSchema
 * Executes all test files in the tests directory
 */
class TestRunner
{
    private int $totalTests = 0;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $results = [];

    public function runAllTests(): void
    {
        echo "🧪 PHPSchema Test Suite\n";
        echo str_repeat("=", 50) . "\n\n";

        $testFiles = $this->discoverTestFiles();
        
        foreach ($testFiles as $testFile) {
            $this->runTestFile($testFile);
        }

        $this->printSummary();
    }

    private function discoverTestFiles(): array
    {
        $testFiles = [];
        $files = scandir(__DIR__);
        
        foreach ($files as $file) {
            if (str_ends_with($file, 'Test.php')) {
                $testFiles[] = $file;
            }
        }
        
        return $testFiles;
    }

    private function runTestFile(string $filename): void
    {
        $className = str_replace('.php', '', $filename);
        
        echo "📋 Running {$className}...\n";
        
        require_once __DIR__ . '/' . $filename;
        
        if (class_exists($className)) {
            $testInstance = new $className();
            $methods = get_class_methods($testInstance);
            
            foreach ($methods as $method) {
                if (str_starts_with($method, 'test')) {
                    $this->runTest($testInstance, $method, $className);
                }
            }
        }
        
        echo "\n";
    }

    private function runTest(object $testInstance, string $method, string $className): void
    {
        $this->totalTests++;
        
        try {
            $testInstance->$method();
            $this->passedTests++;
            echo "  ✅ {$method}\n";
        } catch (Exception $e) {
            $this->failedTests++;
            echo "  ❌ {$method}: " . $e->getMessage() . "\n";
            $this->results[] = [
                'class' => $className,
                'method' => $method,
                'error' => $e->getMessage()
            ];
        }
    }

    private function printSummary(): void
    {
        echo str_repeat("=", 50) . "\n";
        echo "📊 Test Summary:\n";
        echo "   Total: {$this->totalTests}\n";
        echo "   ✅ Passed: {$this->passedTests}\n";
        echo "   ❌ Failed: {$this->failedTests}\n";
        
        if ($this->failedTests > 0) {
            echo "\n💥 Failed Tests:\n";
            foreach ($this->results as $result) {
                echo "   - {$result['class']}::{$result['method']}: {$result['error']}\n";
            }
            exit(1);
        } else {
            echo "\n🎉 All tests passed!\n";
            exit(0);
        }
    }
}

/**
 * Simple assertion functions for testing
 */
function assertEquals($expected, $actual, string $message = '')
{
    if ($expected !== $actual) {
        $errorMessage = $message ?: "Expected " . var_export($expected, true) . ", got " . var_export($actual, true);
        throw new Exception($errorMessage);
    }
}

function assertTrue($condition, string $message = '')
{
    if (!$condition) {
        $errorMessage = $message ?: "Expected true, got false";
        throw new Exception($errorMessage);
    }
}

function assertFalse($condition, string $message = '')
{
    if ($condition) {
        $errorMessage = $message ?: "Expected false, got true";
        throw new Exception($errorMessage);
    }
}

function assertArrayHasKey($key, $array, string $message = '')
{
    if (!array_key_exists($key, $array)) {
        $errorMessage = $message ?: "Array does not have key '{$key}'";
        throw new Exception($errorMessage);
    }
}

function assertEmpty($value, string $message = '')
{
    if (!empty($value)) {
        $errorMessage = $message ?: "Expected empty value, got " . var_export($value, true);
        throw new Exception($errorMessage);
    }
}

function assertNotEmpty($value, string $message = '')
{
    if (empty($value)) {
        $errorMessage = $message ?: "Expected non-empty value";
        throw new Exception($errorMessage);
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $runner = new TestRunner();
    $runner->runAllTests();
}