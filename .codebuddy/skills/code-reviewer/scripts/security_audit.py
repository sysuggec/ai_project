#!/usr/bin/env python3
"""
Security Audit Script for PHP Code
Performs automated security vulnerability scanning
"""

import re
import sys
from pathlib import Path
from typing import List, Dict, Tuple
from dataclasses import dataclass
from enum import Enum


class Severity(Enum):
    CRITICAL = "🔴 Critical"
    HIGH = "🟠 High"
    MEDIUM = "🟡 Medium"
    LOW = "🟢 Low"


@dataclass
class Issue:
    line: int
    severity: Severity
    category: str
    description: str
    recommendation: str


class SecurityAuditor:
    """PHP Security Audit Scanner"""
    
    def __init__(self, file_path: str):
        self.file_path = Path(file_path)
        self.content = ""
        self.lines: List[str] = []
        self.issues: List[Issue] = []
    
    def load_file(self) -> bool:
        """Load and parse the PHP file"""
        try:
            self.content = self.file_path.read_text(encoding='utf-8')
            self.lines = self.content.splitlines()
            return True
        except Exception as e:
            print(f"Error loading file: {e}")
            return False
    
    def scan(self) -> List[Issue]:
        """Run all security checks"""
        self.check_sql_injection()
        self.check_xss_vulnerabilities()
        self.check_dangerous_functions()
        self.check_hardcoded_credentials()
        self.check_file_operations()
        self.check_input_validation()
        self.check_command_injection()
        self.check_unsafe_deserialization()
        self.check_session_security()
        self.check_csrf_protection()
        return self.issues
    
    def add_issue(self, line_num: int, severity: Severity, category: str, 
                  description: str, recommendation: str):
        """Add a security issue"""
        self.issues.append(Issue(
            line=line_num,
            severity=severity,
            category=category,
            description=description,
            recommendation=recommendation
        ))
    
    def check_sql_injection(self):
        """Check for SQL injection vulnerabilities"""
        # Direct variable interpolation in SQL
        patterns = [
            (r'(["\'])(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER).*\$\w+', 
             "SQL injection risk: Variable directly in query string"),
            (r'\$_(GET|POST|REQUEST)\[.*\].*(SELECT|INSERT|UPDATE|DELETE)',
             "SQL injection risk: User input in query without sanitization"),
            (r'->query\s*\(\s*["\'].*\$\w+',
             "SQL injection risk: Direct variable in query method"),
            (r'->exec\s*\(\s*["\'].*\$\w+',
             "SQL injection risk: Direct variable in exec method"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    self.add_issue(
                        i, Severity.CRITICAL, "SQL Injection",
                        desc,
                        "Use prepared statements with parameter binding"
                    )
    
    def check_xss_vulnerabilities(self):
        """Check for XSS vulnerabilities"""
        patterns = [
            (r'echo\s+\$_(GET|POST|REQUEST|COOKIE)',
             "XSS vulnerability: Unescaped user input output"),
            (r'print\s+\$_(GET|POST|REQUEST|COOKIE)',
             "XSS vulnerability: Unescaped user input output"),
            (r'echo\s+\$[a-zA-Z_]\w*(?!.*htmlspecialchars)',
             "Potential XSS: Echo without htmlspecialchars"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            # Skip lines with proper escaping
            if 'htmlspecialchars' in line or 'htmlentities' in line:
                continue
            for pattern, desc in patterns:
                if re.search(pattern, line):
                    self.add_issue(
                        i, Severity.CRITICAL, "XSS",
                        desc,
                        "Use htmlspecialchars() or htmlentities() to escape output"
                    )
    
    def check_dangerous_functions(self):
        """Check for dangerous function usage"""
        dangerous = [
            ('eval\s*\(', "eval()", "Code injection risk"),
            ('exec\s*\(', "exec()", "Command execution risk"),
            ('system\s*\(', "system()", "Command execution risk"),
            ('passthru\s*\(', "passthru()", "Command execution risk"),
            ('shell_exec\s*\(', "shell_exec()", "Command execution risk"),
            ('popen\s*\(', "popen()", "Process handling risk"),
            ('proc_open\s*\(', "proc_open()", "Process handling risk"),
            ('pcntl_exec\s*\(', "pcntl_exec()", "Process execution risk"),
            ('assert\s*\(', "assert()", "Code injection risk"),
            ('create_function\s*\(', "create_function()", "Deprecated and unsafe"),
            ('extract\s*\(', "extract()", "Variable overwrite risk"),
            ('parse_str\s*\(', "parse_str()", "Variable overwrite risk"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, func, risk in dangerous:
                if re.search(pattern, line):
                    self.add_issue(
                        i, Severity.HIGH, "Dangerous Function",
                        f"Use of {func}: {risk}",
                        f"Remove or sanitize {func} usage. Use safer alternatives."
                    )
    
    def check_hardcoded_credentials(self):
        """Check for hardcoded credentials"""
        patterns = [
            (r'(password|passwd|pwd)\s*[=:]\s*["\'][^"\']+["\']',
             "Hardcoded password"),
            (r'(api_key|apikey|secret|token)\s*[=:]\s*["\'][^"\']+["\']',
             "Hardcoded API key/secret"),
            (r'(mysql|database|db_pass)\s*[=:]\s*["\'][^"\']+["\']',
             "Hardcoded database credential"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    # Skip if it's a variable name or in comments
                    if '$_ENV' in line or 'getenv' in line or line.strip().startswith('//'):
                        continue
                    self.add_issue(
                        i, Severity.HIGH, "Hardcoded Credential",
                        desc,
                        "Use environment variables or secure configuration"
                    )
    
    def check_file_operations(self):
        """Check for unsafe file operations"""
        patterns = [
            (r'(include|require|include_once|require_once)\s*\(\s*\$',
             "File inclusion with user input"),
            (r'(fopen|file_get_contents|file_put_contents|readfile)\s*\(\s*\$',
             "File operation with dynamic path"),
            (r'\$_(GET|POST|REQUEST)\[.*\]\s*\.\s*["\']',
             "Path traversal risk: User input in file path"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line):
                    self.add_issue(
                        i, Severity.HIGH, "File Operation",
                        desc,
                        "Validate and sanitize file paths. Use basename() or whitelist approach."
                    )
    
    def check_input_validation(self):
        """Check for missing input validation"""
        patterns = [
            (r'\$_GET\[', "Direct $_GET access without validation"),
            (r'\$_POST\[', "Direct $_POST access without validation"),
            (r'\$_REQUEST\[', "Direct $_REQUEST access without validation"),
            (r'\$_COOKIE\[', "Direct $_COOKIE access without validation"),
        ]
        
        validated_vars = set()
        for i, line in enumerate(self.lines, 1):
            # Track if validation is present
            if 'filter_input' in line or 'filter_var' in line or 'isset' in line:
                validated_vars.update(re.findall(r'\$(\w+)', line))
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line):
                    # Check if there's validation nearby
                    if 'filter' not in line and 'isset' not in line and 'empty' not in line:
                        self.add_issue(
                            i, Severity.MEDIUM, "Input Validation",
                            desc,
                            "Use filter_input() or filter_var() to validate user input"
                        )
                        break
    
    def check_command_injection(self):
        """Check for command injection vulnerabilities"""
        patterns = [
            (r'`.*\$_(GET|POST|REQUEST)',
             "Command injection: User input in backtick execution"),
            (r'backtick|shell_exec.*\$_',
             "Command injection: User input in shell execution"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line, re.IGNORECASE):
                    self.add_issue(
                        i, Severity.CRITICAL, "Command Injection",
                        desc,
                        "Use escapeshellarg() or escapeshellcmd() to escape user input"
                    )
    
    def check_unsafe_deserialization(self):
        """Check for unsafe deserialization"""
        patterns = [
            (r'unserialize\s*\(\s*\$',
             "Unsafe deserialization with user input"),
            (r'unserialize\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)',
             "Unsafe deserialization: Direct user input"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line):
                    self.add_issue(
                        i, Severity.HIGH, "Deserialization",
                        desc,
                        "Use json_decode() instead, or implement __wakeup() validation"
                    )
    
    def check_session_security(self):
        """Check for session security issues"""
        patterns = [
            (r'session_start\s*\(\s*\)',
             "Session without security configuration"),
        ]
        
        for i, line in enumerate(self.lines, 1):
            for pattern, desc in patterns:
                if re.search(pattern, line):
                    # Check if session configuration is present
                    content_above = '\n'.join(self.lines[:i])
                    if 'session_set_cookie_params' not in content_above:
                        self.add_issue(
                            i, Severity.MEDIUM, "Session Security",
                            desc,
                            "Configure session security with session_set_cookie_params()"
                        )
    
    def check_csrf_protection(self):
        """Check for CSRF protection"""
        form_pattern = r'<form[^>]*method\s*=\s*["\']post["\'][^>]*>'
        csrf_indicators = ['csrf', 'token', '_token', 'nonce']
        
        in_form = False
        form_start = 0
        for i, line in enumerate(self.lines, 1):
            if re.search(form_pattern, line, re.IGNORECASE):
                in_form = True
                form_start = i
            
            if in_form and '</form>' in line.lower():
                # Check for CSRF token in form
                form_content = '\n'.join(self.lines[form_start-1:i])
                has_csrf = any(ind in form_content.lower() for ind in csrf_indicators)
                
                if not has_csrf:
                    self.add_issue(
                        form_start, Severity.MEDIUM, "CSRF",
                        "Form missing CSRF token",
                        "Add CSRF token field to prevent cross-site request forgery"
                    )
                in_form = False
    
    def generate_report(self) -> str:
        """Generate security audit report"""
        if not self.issues:
            return "✅ No security issues found!"
        
        # Sort by severity
        severity_order = {Severity.CRITICAL: 0, Severity.HIGH: 1, Severity.MEDIUM: 2, Severity.LOW: 3}
        self.issues.sort(key=lambda x: severity_order[x.severity])
        
        report = []
        report.append(f"\n🔒 Security Audit Report for: {self.file_path}")
        report.append("=" * 60)
        
        # Statistics
        stats = {}
        for sev in Severity:
            count = sum(1 for i in self.issues if i.severity == sev)
            stats[sev] = count
        
        report.append(f"\n📊 Statistics:")
        report.append(f"   Total Issues: {len(self.issues)}")
        for sev, count in stats.items():
            report.append(f"   {sev.value}: {count}")
        
        # Issues by category
        report.append("\n" + "=" * 60)
        report.append("📋 Issues Found:")
        report.append("")
        
        current_severity = None
        for issue in self.issues:
            if issue.severity != current_severity:
                current_severity = issue.severity
                report.append(f"\n{current_severity.value}")
                report.append("-" * 40)
            
            report.append(f"\n  Line {issue.line}: [{issue.category}]")
            report.append(f"  ⚠️  {issue.description}")
            report.append(f"  💡 {issue.recommendation}")
        
        report.append("\n" + "=" * 60)
        return "\n".join(report)


def main():
    if len(sys.argv) < 2:
        print("Usage: python security_audit.py <file_path>")
        sys.exit(1)
    
    file_path = sys.argv[1]
    
    if not Path(file_path).exists():
        print(f"Error: File not found: {file_path}")
        sys.exit(1)
    
    auditor = SecurityAuditor(file_path)
    
    if auditor.load_file():
        issues = auditor.scan()
        print(auditor.generate_report())
        
        # Exit with error code if critical issues found
        critical_count = sum(1 for i in issues if i.severity == Severity.CRITICAL)
        sys.exit(1 if critical_count > 0 else 0)


if __name__ == "__main__":
    main()
