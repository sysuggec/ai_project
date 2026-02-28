---
name: skill-factory
description: This skill should be used when users want to create a new skill or update an existing skill. It provides workflows, scripts, and best practices for skill development. Use when user asks to "create a skill", "build a skill", "make a new skill", or needs help with skill structure and packaging.
---

# Skill Factory

## Overview

This skill enables the creation and management of Claude skills. Skills are modular packages that extend Claude's capabilities with specialized knowledge, workflows, and tools. Use this skill to create, validate, and package new skills efficiently.

## Quick Start

### Create a New Skill

```bash
python3 scripts/init_skill.py <skill-name> --path <output-directory>
```

Example:
```bash
python3 scripts/init_skill.py my-api-helper --path /workspace/.codebuddy/skills
```

### Validate a Skill

```bash
python3 scripts/quick_validate.py <path/to/skill-folder>
```

### Package a Skill

```bash
python3 scripts/package_skill.py <path/to/skill-folder> [output-directory]
```

## Workflow: Creating a New Skill

Follow these steps in order:

### Step 1: Understand Requirements

Ask the user:
- "What functionality should this skill provide?"
- "Can you give examples of how this skill would be used?"
- "What triggers should invoke this skill?"

Document concrete use cases before proceeding.

### Step 2: Choose Skill Name

Naming requirements:
- Hyphen-case identifier (e.g., `data-analyzer`)
- Lowercase letters, digits, and hyphens only
- Max 40 characters
- Must match directory name exactly

### Step 3: Initialize Skill Structure

Run the initialization script:

```bash
python3 scripts/init_skill.py <skill-name> --path /workspace/.codebuddy/skills
```

This creates:
```
skill-name/
├── SKILL.md          # Main skill definition
├── scripts/          # Executable code
├── references/       # Documentation to load as needed
└── assets/           # Files used in output
```

### Step 4: Edit SKILL.md

Complete the SKILL.md with:

1. **YAML Frontmatter**: Update `name` and `description`
   - Description must include WHEN to use the skill
   - Be specific about scenarios, file types, or tasks

2. **Overview**: 1-2 sentences explaining the skill's purpose

3. **Main Content**: Choose a structure:
   - **Workflow-Based**: For sequential processes (Step 1, Step 2...)
   - **Task-Based**: For tool collections (Merge PDFs, Split PDFs...)
   - **Reference/Guidelines**: For standards or specifications
   - **Capabilities-Based**: For integrated systems

4. **Resources**: Document any scripts, references, or assets

### Step 5: Add Resources (Optional)

#### scripts/
- Executable Python/Bash scripts
- For automation, data processing, file operations
- Can be executed without loading into context

#### references/
- Documentation to load as needed
- API docs, schemas, detailed guides
- Keeps SKILL.md lean

#### assets/
- Files used in output (templates, images, fonts)
- Not loaded into context
- Copied or modified for final output

### Step 6: Validate and Package

Validate the skill:
```bash
python3 scripts/quick_validate.py /workspace/.codebuddy/skills/<skill-name>
```

Package for distribution:
```bash
python3 scripts/package_skill.py /workspace/.codebuddy/skills/<skill-name> ./dist
```

## Skill Structure Best Practices

### SKILL.md Writing Guidelines

1. Use **imperative/infinitive form** (verb-first), not second person
   - ✅ "To accomplish X, do Y"
   - ❌ "You should do X"

2. Keep SKILL.md under 5,000 words
   - Move detailed content to `references/`

3. Include concrete examples with realistic user requests

4. Reference bundled resources appropriately

### Description Quality

The description determines when the skill triggers:
- ✅ "This skill should be used when processing Excel files, reading xlsx/xls spreadsheets, or extracting data from Excel workbooks."
- ❌ "Excel skill for spreadsheets."

### Progressive Disclosure

Skills use three-level loading:
1. **Metadata** (name + description) - Always loaded
2. **SKILL.md body** - When skill triggers
3. **Bundled resources** - As needed

## Common Patterns

### Pattern 1: Workflow-Based Skill

```markdown
## Workflow Decision Tree

1. Is this a new file? → Go to "Creating"
2. Is this an existing file? → Go to "Reading"
3. Is this a modification? → Go to "Editing"

## Creating
Steps for creating new files...

## Reading
Steps for reading existing files...

## Editing
Steps for editing files...
```

### Pattern 2: Task-Based Skill

```markdown
## Quick Start
Common operations overview...

## Task 1: Operation Name
Description and steps...

## Task 2: Another Operation
Description and steps...
```

## Scripts Reference

### init_skill.py

Creates a new skill directory with template files.

```bash
python3 scripts/init_skill.py <skill-name> --path <path>
```

Options:
- `skill-name`: Hyphen-case identifier
- `--path`: Output directory (default: current directory)

### quick_validate.py

Validates skill structure and metadata.

```bash
python3 scripts/quick_validate.py <path/to/skill-folder>
```

Checks:
- YAML frontmatter format
- Required fields (name, description)
- Skill naming conventions
- Directory structure

### package_skill.py

Creates a distributable zip file.

```bash
python3 scripts/package_skill.py <path/to/skill-folder> [output-directory]
```

Features:
- Validates before packaging
- Creates zip with proper structure
- Names zip after skill

## Troubleshooting

### Skill Not Triggering

1. Check description includes WHEN to use
2. Verify skill name matches directory
3. Ensure SKILL.md has valid frontmatter

### Validation Errors

1. **Missing frontmatter**: Add YAML block at top
2. **Invalid description**: Make more specific
3. **Wrong structure**: Check directory naming

### Package Fails

1. Run validation first: `quick_validate.py`
2. Check SKILL.md exists
3. Verify directory structure
