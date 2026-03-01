---
name: code-reviewer
description: This skill should be used when performing code reviews. It provides systematic code quality checks following coding standards and security best practices. Use this skill when reviewing PHP code, conducting security audits, or generating code review reports with statistics and line-level issue tracking.
---

# Code Reviewer

## Overview

A systematic code review skill that analyzes code quality against project coding standards and security best practices. It provides structured feedback with statistics, line numbers, and actionable recommendations.

## When to Use This Skill

Use this skill when:
- Performing code review on PHP files
- Conducting security audits
- Validating code against PSR standards
- Checking for coding style violations
- Generating review reports with statistics
- User requests "code review", "review this code", or similar

## Code Review Process

### Step 1: Load Project Coding Standards

Before reviewing, load the project's coding standards using `read_rules` tool:

```
Use read_rules tool with ruleNames: "coding-style"
```

The project follows PSR-1, PSR-2, and PSR-4 standards with additional security requirements.


### Step 2: Manual Code Quality Review

Review the following aspects systematically:

#### 2.1 Coding Standards Compliance

Check against PSR standards:
- [ ] File uses `<?php` tag only
- [ ] UTF-8 encoding without BOM
- [ ] `declare(strict_types=1);` present
- [ ] Class names use PascalCase
- [ ] Method names use camelCase
- [ ] Constants use UPPER_CASE
- [ ] 4 spaces indentation (no tabs)
- [ ] Lines under 120 characters
- [ ] Proper namespace and use statements

#### 2.2 Code Organization

- [ ] Namespace matches directory structure (PSR-4)
- [ ] Use statements are alphabetically ordered
- [ ] Proper class structure: constants → properties → constructor → public → private
- [ ] Explicit access modifiers declared
- [ ] Type declarations for all parameters and return types

#### 2.3 Best Practices

- [ ] Methods under 50 lines
- [ ] Single responsibility per method
- [ ] No deep nesting (max 3 levels)
- [ ] No code duplication
- [ ] Meaningful variable/method names
- [ ] Dependency injection used
- [ ] Proper exception handling

#### 2.4 Security Review

Refer to `/workspace/.codebuddy/skills/code-reviewer/references/security_checklist.md` for detailed security checks:

- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (output escaping)
- [ ] Input validation and filtering
- [ ] No hardcoded credentials
- [ ] No dangerous functions (eval, exec, etc.)
- [ ] Proper file operation handling
- [ ] Session security

### Step 3: Generate Review Report

Produce a structured report with:

#### Required Report Format

```markdown
# Code Review Report

## File: <file_path>

### Statistics
- Total Lines: <count>
- Issues Found: <count>
- Critical: <count> | High: <count> | Medium: <count> | Low: <count>

### Issues by Category

#### 🔴 Critical Issues

| Line | Type | Description | Recommendation |
|------|------|-------------|----------------|
| XX   | Security | SQL injection risk | Use prepared statements |

#### 🟠 High Priority Issues

| Line | Type | Description | Recommendation |
|------|------|-------------|----------------|
| XX   | Standard | Missing type declaration | Add parameter type |

#### 🟡 Medium Priority Issues

| Line | Type | Description | Recommendation |
|------|------|-------------|----------------|
| XX   | Style | Line exceeds 120 chars | Break into multiple lines |

#### 🟢 Low Priority Issues

| Line | Type | Description | Recommendation |
|------|------|-------------|----------------|
| XX   | Best Practice | Complex condition | Extract to variable |

### Summary

<Overall assessment and priority recommendations>

### Code Quality Score

- Standards Compliance: X/10
- Security Score: X/10
- Maintainability: X/10
- Overall: X/10
```

## Issue Severity Levels

| Level | Description |
|-------|-------------|
| 🔴 Critical | Security vulnerabilities, breaking bugs |
| 🟠 High | Major standard violations, potential bugs |
| 🟡 Medium | Style issues, minor standard violations |
| 🟢 Low | Best practice suggestions, minor improvements |

## Resources

### scripts/
- `security_audit.py` - Automated security vulnerability scanner

### references/
- `security_checklist.md` - Comprehensive security review checklist
