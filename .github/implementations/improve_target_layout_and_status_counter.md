# Improve Target Section Layout and Add Status Counter

## Problem
User wants:
1. More breathing room between the target counter and the form fields
2. A status counter next to the status/progress box (similar to target numbering)

## Current Structure
```
Target 1
[Target description input] [Status/Progress input] [Delete]
```

## Desired Structure
```
Target 1                     Status 1
[Target description input]   [Status/Progress input] [Delete]
```

## Tasks
- [ ] Add more spacing between target header and input fields
- [ ] Add status counter label above the status/progress input field
- [ ] Update both PHP existing targets and JavaScript new targets template
- [ ] Ensure consistent spacing and alignment

## Implementation
1. Modify the target item structure to include status counter
2. Add better spacing with margin classes
3. Update JavaScript template to match
4. Ensure responsive design works properly
