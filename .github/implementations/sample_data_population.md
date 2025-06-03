# Sample Data Population for Programs and Submissions

## Overview
This task involves populating sample data into the database for:
- 5 sample programs linked to testagency (user_id: 12)
- Sample submissions for these programs
- Ratings, targets, and status data

## Database Context
- **testagency user_id**: 12
- **agency_name**: testagency  
- **sector_id**: 1 (Forestry)
- **agency_group_id**: 0
- **Available periods**: 11 (2025 Q5), 12 (2025 Q6), etc.

## Tasks

### ✅ Task 1: Analyze Database Structure
- [x] Identify testagency user (user_id: 12)
- [x] Check available sectors (Forestry - sector_id: 1)
- [x] Check available reporting periods
- [x] Understand program and submission table structure

### ✅ Task 2: Create Sample Programs
- [x] Insert 5 diverse forestry programs
- [x] Link all programs to testagency (owner_agency_id: 12)
- [x] Set appropriate start/end dates
- [x] Include varied program types (conservation, management, research, etc.)

### ✅ Task 3: Create Sample Submissions
- [x] Create submissions for each program
- [x] Link to recent reporting periods (11, 12)
- [x] Include realistic content_json data with:
  - [x] Ratings (1-5 scale)
  - [x] Targets (numerical goals)
  - [x] Status information
  - [x] Progress data

### ✅ Task 4: Validate Data
- [x] Verify all foreign key relationships
- [x] Check data consistency
- [x] Ensure realistic sample content

## Program Ideas
1. **Forest Conservation Initiative** - Protecting primary forest areas
2. **Sustainable Timber Management** - Responsible logging practices  
3. **Reforestation Program** - Planting new forest areas
4. **Wildlife Habitat Protection** - Preserving biodiversity
5. **Forest Research & Development** - Scientific studies and innovation

## Submission Content Structure
Each submission should include:
- Overall program rating (1-5)
- Target metrics (hectares, trees planted, etc.)
- Current status (on-track, delayed, completed)
- Progress percentages
- Challenges and achievements

## ✅ COMPLETED SUMMARY

Successfully populated the database with comprehensive sample data:

### Programs Created (5 total)
1. **Forest Conservation Initiative** (ID: 165) - Protection program
2. **Sustainable Timber Management Program** (ID: 166) - Resource management
3. **Reforestation and Restoration Project** (ID: 167) - Restoration program  
4. **Wildlife Habitat Protection Scheme** (ID: 168) - Biodiversity conservation
5. **Forest Research & Development Initiative** (ID: 169) - Research program

### Submissions Created (10 total)
- 2 submissions per program (for periods Q5-2025 and Q6-2025)
- Ratings range from 3-5 (realistic distribution)
- Progress percentages vary from 55%-100%
- Diverse status types: on-track, delayed, completed, ahead-of-schedule, in-progress
- Rich content with targets, achievements, challenges, and goals

### Data Quality Features
- **Realistic Targets**: Hectares, tree counts, research projects, etc.
- **Varied Status**: Different programs show different performance levels
- **Progress Tracking**: Improvements shown between quarters
- **Detailed Content**: Achievements, challenges, and next steps included
- **Proper Relationships**: All foreign keys correctly linked to testagency (user_id: 12)

All data is now ready for testing dashboard functionality and reporting features.
