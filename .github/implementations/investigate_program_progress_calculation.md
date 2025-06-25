# Investigate Program Progress Calculation Methods

## Objective
Understand how program progress percentages should be calculated based on available data in the PCDS2030 dashboard system.

## Tasks
- [x] Analyze current data structure for progress indicators
- [x] Review program submission content and rating system
- [x] Examine program timeline vs submission activity
- [x] Identify possible calculation methods
- [x] Recommend best approach for progress calculation
- [x] Document the calculation logic

## Current Data Available:
From database investigation:
- Programs have `start_date` and `end_date` fields in DATE format
- Program submissions contain rating system stored in `content_json` field:
  - "target-achieved" (6 submissions)
  - "on-track-yearly" (20 submissions)  
  - "not-started" (29 submissions)
- Submission content includes targets and status descriptions
- Multiple submissions per program across reporting periods
- Timeline-based reporting (quarterly periods)
- Program durations vary significantly: 40 days (short-term) to 2190 days (6-year programs)

## Database Query Analysis:
Timeline-based progress calculation using SQL:
```sql
CASE 
    WHEN start_date IS NULL OR end_date IS NULL THEN NULL
    WHEN CURDATE() < start_date THEN 0
    WHEN CURDATE() > end_date THEN 100
    ELSE ROUND((DATEDIFF(CURDATE(), start_date) / DATEDIFF(end_date, start_date)) * 100, 1)
END as timeline_progress_percentage
```

Sample results show realistic progress:
- Long-term programs (2025-2030): 8.0% progress
- Mid-term programs (2025 only): 48.1% progress  
- Short-term programs (40 days): 57.5% progress
- Completed programs: 100.0% progress

## Possible Progress Calculation Methods:

### 1. Timeline-based (Recommended for Base Calculation)
**Formula**: `(current_date - start_date) / (end_date - start_date) * 100`
- **Pros**: Objective, consistent, easy to understand, reflects actual time elapsed
- **Cons**: Doesn't reflect performance quality or actual achievements
- **Best for**: Visual timeline progress, consistent comparison across programs

### 2. Rating-based (Performance Modifier)
**Mapping**:
- "target-achieved": 85-100% (High performance)
- "on-track-yearly": 60-85% (Good performance)  
- "not-started": 0-25% (Poor performance)
- **Pros**: Reflects actual performance assessment by agencies
- **Cons**: Subjective, may not correlate with timeline
- **Best for**: Performance indicators, dashboard alerts

### 3. Hybrid Approach (RECOMMENDED)
**Formula**: `base_timeline_progress + performance_modifier`
```javascript
// Base calculation
const timelineProgress = (currentDate - startDate) / (endDate - startDate) * 100;

// Performance modifier based on latest rating
const performanceModifier = {
    'target-achieved': 1.2,    // Boost by 20%
    'on-track-yearly': 1.0,    // No change
    'not-started': 0.7         // Reduce by 30%
};

// Final progress (capped at 100%)
const finalProgress = Math.min(timelineProgress * modifier, 100);
```

- **Pros**: Combines objective timeline with subjective performance
- **Cons**: More complex, requires careful calibration
- **Best for**: Comprehensive progress representation

### 4. Submission-based 
Based on submission frequency vs expected periods
- **Pros**: Reflects reporting compliance
- **Cons**: Doesn't indicate actual progress quality
- **Best for**: Administrative tracking

### 5. Target-based
Based on completion of defined targets within submissions
- **Pros**: Reflects specific achievement milestones
- **Cons**: Requires structured target definition and tracking
- **Best for**: Detailed performance analysis

## Final Recommendation:

**Use Hybrid Approach for Initiative Progress Dashboards:**

1. **Primary Progress Bar**: Timeline-based calculation for consistent visual reference
2. **Performance Indicator**: Color-coding and badges based on latest rating
3. **Tooltip/Details**: Show both timeline progress and performance rating

**Implementation Example:**
```php
function calculateProgramProgress($program) {
    // Timeline calculation
    $timelineProgress = calculateTimelineProgress($program['start_date'], $program['end_date']);
    
    // Performance modifier
    $ratingMultiplier = [
        'target-achieved' => 1.15,
        'on-track-yearly' => 1.0,
        'not-started' => 0.8
    ];
    
    $modifier = $ratingMultiplier[$program['latest_rating']] ?? 1.0;
    
    return [
        'timeline_progress' => $timelineProgress,
        'performance_progress' => min($timelineProgress * $modifier, 100),
        'rating' => $program['latest_rating'],
        'display_progress' => min($timelineProgress * $modifier, 100)
    ];
}
```

This approach provides stakeholders with meaningful progress information that combines objective timeline data with subjective performance assessments.
