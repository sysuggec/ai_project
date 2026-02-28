---
name: bdd-skill
description: This skill should be used when practicing Behavior-Driven Development (BDD). Use when writing feature specs, using Gherkin syntax (Given/When/Then), working with Behat/Codeception, or when user mentions "BDD", "behavior-driven development", "feature test", "scenario", "gherkin", or "acceptance test".
---

# BDD (Behavior-Driven Development)

## Overview

This skill guides the Behavior-Driven Development workflow: describe expected behavior in human-readable scenarios, implement step definitions, then write the code. BDD bridges the gap between technical and non-technical stakeholders by using a shared vocabulary.

## The BDD Workflow

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│   ┌──────────┐    ┌──────────┐    ┌──────────┐         │
│   │  DEFINE  │ ─► │ AUTOMATE │ ─► │ IMPLEMENT│         │
│   │ Scenario │    │  Steps   │    │   Code   │         │
│   └──────────┘    └──────────┘    └──────────┘         │
│        ▲                                 │              │
│        └─────────────────────────────────┘              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## Gherkin Syntax

BDD scenarios use Gherkin syntax with these keywords:

| Keyword | Purpose | Example |
|---------|---------|---------|
| Feature | High-level description | Feature: User authentication |
| Scenario | Specific test case | Scenario: User logs in successfully |
| Given | Precondition/Setup | Given I am on the login page |
| When | Action/Event | When I enter valid credentials |
| Then | Expected outcome | Then I should see the dashboard |
| And/But | Additional steps | And I should see "Welcome" |

## Workflow

### Step 1: DEFINE - Write the Scenario

Start by describing the expected behavior in business terms:

```gherkin
Feature: User Authentication
  As a registered user
  I want to log in to the system
  So that I can access my account

  Scenario: User logs in successfully
    Given I am a registered user with email "user@example.com"
    And I am on the login page
    When I fill in "email" with "user@example.com"
    And I fill in "password" with "correct_password"
    And I press "Login"
    Then I should see "Welcome back"
    And I should be on the dashboard page

  Scenario: User fails to login with wrong password
    Given I am a registered user with email "user@example.com"
    And I am on the login page
    When I fill in "email" with "user@example.com"
    And I fill in "password" with "wrong_password"
    And I press "Login"
    Then I should see "Invalid credentials"
    And I should be on the login page
```

### Step 2: AUTOMATE - Create Step Definitions

Implement the steps in PHP:

```php
// features/bootstrap/FeatureContext.php
use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

class FeatureContext extends MinkContext implements Context
{
    private User $user;

    /**
     * @Given I am a registered user with email :email
     */
    public function iAmARegisteredUserWithEmail(string $email): void
    {
        $this->user = new User($email, 'correct_password');
        // Store in database or use mock
    }

    /**
     * @Then I should be on the dashboard page
     */
    public function iShouldBeOnTheDashboardPage(): void
    {
        $this->assertPageAddress('/dashboard');
    }
}
```

### Step 3: IMPLEMENT - Write Production Code

Write the minimal code to make scenarios pass:

```php
// src/Service/AuthService.php
class AuthService
{
    public function login(string $email, string $password): AuthResult
    {
        $user = $this->userRepository->findByEmail($email);
        
        if ($user === null || !$user->verifyPassword($password)) {
            return AuthResult::failed('Invalid credentials');
        }
        
        $this->session->set('user_id', $user->getId());
        return AuthResult::success($user);
    }
}
```

Run the scenario:
```bash
vendor/bin/behat features/authentication.feature
```

## BDD Best Practices

### Write Declarative Scenarios

```gherkin
# ✅ Good - Declarative (what, not how)
Scenario: User completes checkout
  Given I have items in my cart
  When I complete the checkout process
  Then I should receive an order confirmation

# ❌ Bad - Imperative (too many UI details)
Scenario: User completes checkout
  Given I click on "Add to Cart"
  And I click on "Cart"
  And I click on "Checkout"
  And I fill in "card_number" with "4111111111111111"
  And I fill in "expiry" with "12/25"
  And I click on "Submit"
  Then I should see "Order confirmed"
```

### Use Examples Tables

```gherkin
Scenario Outline: User registration validation
  Given I am on the registration page
  When I fill in "email" with "<email>"
  And I press "Register"
  Then I should see "<message>"

  Examples:
    | email              | message                    |
    | invalid            | Please enter a valid email |
    |                    | Email is required          |
    | existing@test.com  | Email already exists       |
```

### Organize Scenarios by Business Value

```gherkin
Feature: Shopping Cart
  Rule: Users can add items to cart

  Scenario: Add single item
    # ...

  Scenario: Add multiple items
    # ...

  Rule: Users can remove items from cart

  Scenario: Remove item
    # ...
```

### Keep Scenarios Independent

```gherkin
# ✅ Good - Self-contained scenario
Scenario: User views product details
  Given there is a product "Widget" priced at $9.99
  When I view the product "Widget"
  Then I should see the price "$9.99"

# ❌ Bad - Depends on previous scenario
Scenario: User views product details
  When I view the product
  Then I should see the price
```

## PHP BDD Frameworks

| Framework | Best For | Notes |
|-----------|----------|-------|
| Behat | Full BDD stack | Standard PHP BDD framework |
| Codeception | BDD + Testing | Fluent syntax, good for APIs |
| Kahlan | BDD + TDD | Jasmine-style specs |
| Atoum | Unit + BDD | Fluent assertions |

### Behat Configuration

```yaml
# behat.yml
default:
  extensions:
    Behat\MinkExtension:
      base_url: 'http://localhost:8000'
      sessions:
        default:
          goutte: ~
  suites:
    default:
      contexts:
        - FeatureContext
        - Behat\MinkExtension\Context\MinkContext
```

### Codeception BDD Style

```php
// tests/acceptance/LoginCest.php
class LoginCest
{
    public function loginSuccessfully(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'user@example.com');
        $I->fillField('password', 'secret');
        $I->click('Login');
        $I->see('Welcome back');
        $I->seeInCurrentUrl('/dashboard');
    }
}
```

## Decision Tree: Unit Test vs BDD Scenario

```
┌────────────────────────────────────────────┐
│ Is this a business-facing requirement?     │
└──────────────┬─────────────────────────────┘
               │
       ┌───────┴───────┐
       │               │
      Yes              No
       │               │
       ▼               ▼
┌─────────────┐  ┌─────────────┐
│ Write BDD   │  │ Write Unit  │
│ Scenario    │  │ Test        │
└─────────────┘  └─────────────┘
```

## Common Patterns

### Background for Shared Setup

```gherkin
Feature: User Profile

  Background:
    Given I am logged in as "john@example.com"

  Scenario: View profile
    When I visit my profile
    Then I should see my email address

  Scenario: Update profile
    When I update my name to "John Doe"
    Then I should see "John Doe" on my profile
```

### Tags for Categorization

```gherkin
@authentication @critical
Scenario: User logs in
  # ...

@javascript @slow
Scenario: File upload
  # ...
```

Run tagged scenarios:
```bash
vendor/bin/behat --tags="@critical"
vendor/bin/behat --tags="~@slow"  # Exclude slow tests
```

### Hooks for Setup/Teardown

```php
class FeatureContext implements Context
{
    /** @BeforeScenario */
    public function beforeScenario(): void
    {
        // Reset database, clear cache, etc.
    }

    /** @AfterScenario */
    public function afterScenario(): void
    {
        // Cleanup
    }

    /** @BeforeScenario @database */
    public function beforeDatabaseScenario(): void
    {
        // Only runs for scenarios tagged @database
    }
}
```

## Troubleshooting

### Scenario Too Complex

- Split into multiple scenarios
- Use background for common setup
- Focus on one behavior per scenario

### Steps Too Technical

- Rewrite in business language
- Create higher-level step definitions
- Involve stakeholders in writing scenarios

### Flaky Tests

- Avoid relying on timing
- Use deterministic data
- Mock external services

## Summary Checklist

Before committing BDD scenarios:

- [ ] Scenarios describe business behavior
- [ ] Gherkin syntax is correct
- [ ] Scenarios are independent
- [ ] Steps are reusable
- [ ] All scenarios pass
- [ ] Scenarios are readable by non-technical stakeholders
- [ ] Edge cases are covered
