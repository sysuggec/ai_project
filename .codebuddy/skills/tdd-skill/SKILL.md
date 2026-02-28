---
name: tdd-skill
description: This skill should be used when practicing Test-Driven Development (TDD). Use when writing tests first, following red-green-refactor cycle, or when user mentions "TDD", "test-driven development", "write test first", or "red green refactor".
---

# TDD (Test-Driven Development)

## Overview

This skill guides the Test-Driven Development workflow: write a failing test first, implement minimal code to pass, then refactor. This ensures code is testable, well-designed, and thoroughly covered.

## The Red-Green-Refactor Cycle

```
┌─────────────────────────────────────────────┐
│                                             │
│   ┌─────────┐     ┌─────────┐     ┌───────┐ │
│   │  RED    │ ──► │  GREEN  │ ──► │REFACTOR│ │
│   │(Failing)│     │(Passing)│     │(Clean) │ │
│   └─────────┘     └─────────┘     └───────┘ │
│        ▲                                  │  │
│        └──────────────────────────────────┘  │
│                                             │
└─────────────────────────────────────────────┘
```

## Workflow

### Step 1: RED - Write a Failing Test

Before writing any production code, write a test that:

1. **Describes expected behavior** - What should the code do?
2. **Fails for the right reason** - Not a syntax error, but assertion failure
3. **Is minimal** - Test only one thing

```php
// Example: PHP with PHPUnit
public function testAddReturnsSum(): void
{
    $calculator = new Calculator();
    $result = $calculator->add(2, 3);
    $this->assertEquals(5, $result);
}
```

Run the test - it MUST fail:
```
phpunit tests/CalculatorTest.php
# Error: Class "Calculator" does not exist
# or
# Failure: Failed asserting that null matches expected 5
```

### Step 2: GREEN - Make It Pass

Write the MINIMAL code to make the test pass:

1. **Don't over-engineer** - Just make it work
2. **Hardcode if needed** - Optimization comes later
3. **Keep it simple** - Focus on passing, not perfection

```php
// Minimal implementation
class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
```

Run the test - it MUST pass:
```
phpunit tests/CalculatorTest.php
# OK (1 test, 1 assertion)
```

### Step 3: REFACTOR - Clean the Code

Now improve the code while keeping tests green:

1. **Remove duplication** - DRY principle
2. **Improve naming** - Clear, intention-revealing names
3. **Simplify logic** - Reduce complexity
4. **Extract methods** - Better organization

```php
// Before refactor
public function calculate(int $a, int $b, string $op): int
{
    if ($op === 'add') return $a + $b;
    if ($op === 'subtract') return $a - $b;
    return 0;
}

// After refactor
public function calculate(int $a, int $b, string $operation): int
{
    return match ($operation) {
        'add' => $this->add($a, $b),
        'subtract' => $this->subtract($a, $b),
        default => throw new InvalidArgumentException("Unknown operation: {$operation}"),
    };
}
```

Run tests after each change - they MUST stay green.

### Step 4: Repeat

Continue the cycle for each new feature or edge case.

## TDD Best Practices

### Test Naming

Use descriptive names that explain the behavior:

```php
// ✅ Good
public function testWithdrawThrowsExceptionWhenInsufficientFunds(): void
public function testTransferDeductsFromSourceAndAddsToDestination(): void

// ❌ Bad
public function testWithdraw(): void
public function test1(): void
```

### Test Structure: AAA Pattern

```php
public function testWithdrawReducesBalance(): void
{
    // Arrange - Set up test data
    $account = new BankAccount(100);
    
    // Act - Execute the behavior
    $account->withdraw(30);
    
    // Assert - Verify the outcome
    $this->assertEquals(70, $account->getBalance());
}
```

### One Assert Per Test (Guideline)

```php
// ✅ Preferred - Single responsibility
public function testWithdrawReturnsAmount(): void
{
    $account = new BankAccount(100);
    $this->assertEquals(30, $account->withdraw(30));
}

public function testWithdrawReducesBalance(): void
{
    $account = new BankAccount(100);
    $account->withdraw(30);
    $this->assertEquals(70, $account->getBalance());
}

// ❌ Avoid - Multiple assertions
public function testWithdraw(): void
{
    $account = new BankAccount(100);
    $this->assertEquals(30, $account->withdraw(30));
    $this->assertEquals(70, $account->getBalance());
}
```

### Test Edge Cases

Always test boundaries and edge cases:

```php
public function testWithdrawThrowsExceptionForNegativeAmount(): void
{
    $account = new BankAccount(100);
    $this->expectException(InvalidArgumentException::class);
    $account->withdraw(-10);
}

public function testWithdrawThrowsExceptionWhenAmountExceedsBalance(): void
{
    $account = new BankAccount(100);
    $this->expectException(InsufficientFundsException::class);
    $account->withdraw(150);
}

public function testWithdrawAllowsZeroAmount(): void
{
    $account = new BankAccount(100);
    $account->withdraw(0);
    $this->assertEquals(100, $account->getBalance());
}
```

## Common TDD Patterns

### Fake It Till You Make It

```php
// Test
$this->assertEquals(5, $calculator->add(2, 3));

// First implementation (fake it)
public function add(int $a, int $b): int
{
    return 5;
}

// Add another test
$this->assertEquals(3, $calculator->add(1, 2));

// Now implement properly
public function add(int $a, int $b): int
{
    return $a + $b;
}
```

### Triangulation

Use multiple examples to drive generalization:

```php
// Test 1
$this->assertEquals(5, $calculator->add(2, 3));

// Implementation could be: return 5;

// Test 2
$this->assertEquals(7, $calculator->add(3, 4));

// Now must implement correctly
public function add(int $a, int $b): int
{
    return $a + $b;
}
```

### State-Based Testing vs Behavior Testing

```php
// State-based: Check resulting state
$account->withdraw(30);
$this->assertEquals(70, $account->getBalance());

// Behavior-based: Check interactions (use sparingly)
$mockLogger = $this->createMock(Logger::class);
$mockLogger->expects($this->once())
    ->method('log')
    ->with('Withdrawal: 30');
$account->setLogger($mockLogger);
$account->withdraw(30);
```

## PHP Testing Tools

| Tool | Usage |
|------|-------|
| PHPUnit | Unit testing framework |
| Pest | Modern testing framework |
| Prophecy | Mock object framework |
| Mockery | Alternative mocking library |

### Running Tests

```bash
# Run all tests
phpunit

# Run specific test file
phpunit tests/CalculatorTest.php

# Run with coverage
phpunit --coverage-html coverage/

# Run filtered tests
phpunit --filter testWithdraw
```

## Decision Tree: When to Write Tests

```
┌──────────────────────────────────────┐
│ Do you understand the requirement?   │
└──────────────┬───────────────────────┘
               │
      ┌────────┴────────┐
      │                 │
     Yes               No
      │                 │
      ▼                 ▼
┌───────────┐    ┌─────────────────┐
│ Write the │    │ Clarify first   │
│ test first│    │ with examples   │
└───────────┘    └─────────────────┘
```

## Troubleshooting

### Test Won't Fail

- Verify the assertion is checking something meaningful
- Ensure test is actually running
- Check for typos in test method name

### Too Hard to Write Test

- Code may need refactoring
- Consider dependency injection
- Extract interfaces for mocking

### Tests Are Slow

- Mock external dependencies (database, API)
- Use in-memory databases for testing
- Avoid file I/O in unit tests

## Summary Checklist

Before committing code:

- [ ] All tests pass (GREEN)
- [ ] New code has test coverage
- [ ] Edge cases are tested
- [ ] Code is refactored and clean
- [ ] No commented-out tests
- [ ] Test names describe behavior
