# Multi-Level Program Numbering System Implementation

## Problem
Current system only supports `31.2` format. Need to support:
- `31.2` (Level 1: Initiative.Sequence)
- `31.2A` (Level 2: Initiative.Sequence + Letter)
- `31.2A.1` (Level 3: Initiative.Sequence + Letter.SubSequence)

## Solution
Update the numbering system to support hierarchical multi-level program numbers while maintaining backward compatibility.

## Implementation Steps

### Phase 1: Update Core Constants and Patterns
- [x] Update regex patterns to support all three formats
- [x] Add new constants for letter suffixes and sub-sequences
- [x] Update validation functions
- [x] Maintain backward compatibility

### Phase 2: Update Generation Logic
- [x] Update `generate_next_program_number()` to detect format level
- [x] Add functions for generating each format level
- [x] Update sequence detection logic
- [x] Add helper functions for format parsing

### Phase 3: Update All Related Files
- [x] Update validation in all files using the centralized functions
- [x] Test all format scenarios
- [x] Ensure database compatibility

### Phase 4: Testing
- [x] Test format `31.2` (existing)
- [x] Test format `31.2A` (new level 2)
- [x] Test format `31.2A.1` (new level 3)
- [x] Test mixed formats within same initiative
- [x] Test validation and generation

## Supported Formats
1. **Level 1**: `31.2` - Basic initiative.sequence
2. **Level 2**: `31.2A` - With letter suffix (A-Z)
3. **Level 3**: `31.2A.1` - With sub-sequence (1-999)

## Benefits
- Supports complex project hierarchies
- Maintains backward compatibility
- Flexible numbering system
- Centralized validation and generation
